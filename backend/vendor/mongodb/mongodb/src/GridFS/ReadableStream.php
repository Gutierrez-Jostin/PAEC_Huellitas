<?php
/*
 * Copyright 2016-present MongoDB, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace MongoDB\GridFS;

use MongoDB\BSON\Binary;
use MongoDB\Driver\CursorInterface;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\GridFS\Exception\CorruptFileException;

use function assert;
use function ceil;
use function floor;
use function is_integer;
use function is_object;
use function property_exists;
use function sprintf;
use function strlen;
use function substr;

/**
 * ReadableStream abstracts the process of reading a GridFS file.
 *
 * @internal
 */
final class ReadableStream
{
    private ?string $buffer = null;

    private int $bufferOffset = 0;

    private int $chunkSize;

    private int $chunkOffset = 0;

    private ?CursorInterface $chunksIterator = null;

    private int $expectedLastChunkSize = 0;

    private int $length;

    private int $numChunks = 0;

    /**
     * Constructs a readable GridFS stream.
     *
     * @param CollectionWrapper $collectionWrapper GridFS collection wrapper
     * @param object            $file              GridFS file document
     * @throws CorruptFileException
     */
    public function __construct(private CollectionWrapper $collectionWrapper, private object $file)
    {
        if (! isset($file->chunkSize) || ! is_integer($file->chunkSize) || $file->chunkSize < 1) {
            throw new CorruptFileException('file.chunkSize is not an integer >= 1');
        }

        if (! isset($file->length) || ! is_integer($file->length) || $file->length < 0) {
            throw new CorruptFileException('file.length is not an integer > 0');
        }

        if (! isset($file->_id) && ! property_exists($file, '_id')) {
            throw new CorruptFileException('file._id does not exist');
        }

        $this->chunkSize = $file->chunkSize;
        $this->length = $file->length;

        if ($this->length > 0) {
            $this->numChunks = (integer) ceil($this->length / $this->chunkSize);
            $this->expectedLastChunkSize = $this->length - (($this->numChunks - 1) * $this->chunkSize);
        }
    }

    /**
     * Return internal properties for debugging purposes.
     *
     * @see https://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.debuginfo
     */
    public function __debugInfo(): array
    {
        return [
            'bucketName' => $this->collectionWrapper->getBucketName(),
            'databaseName' => $this->collectionWrapper->getDatabaseName(),
            'file' => $this->file,
        ];
    }

    public function close(): void
    {
        // Nothing to do
    }

    public function getFile(): object
    {
        return $this->file;
    }

    public function getSize(): int
    {
        return $this->length;
    }

    /**
     * Return whether the current read position is at the end of the stream.
     */
    public function isEOF(): bool
    {
        if ($this->chunkOffset === $this->numChunks - 1) {
            return $this->bufferOffset >= $this->expectedLastChunkSize;
        }

        return $this->chunkOffset >= $this->numChunks;
    }

    /**
     * Read bytes from the stream.
     *
     * Note: this method may return a string smaller than the requested length
     * if data is not available to be read.
     *
     * @param integer $length Number of bytes to read
     * @throws InvalidArgumentException if $length is negative
     */
    public function readBytes(int $length): string
    {
        if ($length < 0) {
            throw new InvalidArgumentException(sprintf('$length must be >= 0; given: %d', $length));
        }

        if ($this->chunksIterator === null) {
            $this->initChunksIterator();
        }

        if ($this->buffer === null && ! $this->initBufferFromCurrentChunk()) {
            return '';
        }

        assert($this->buffer !== null);

        $data = '';

        while (strlen($data) < $length) {
            if ($this->bufferOffset >= strlen($this->buffer) && ! $this->initBufferFromNextChunk()) {
                break;
            }

            $initialDataLength = strlen($data);
            $data .= substr($this->buffer, $this->bufferOffset, $length - $initialDataLength);
            $this->bufferOffset += strlen($data) - $initialDataLength;
        }

        return $data;
    }

    /**
     * Seeks the chunk and buffer offsets for the next read operation.
     *
     * @throws InvalidArgumentException if $offset is out of range
     */
    public function seek(int $offset): void
    {
        if ($offset < 0 || $offset > $this->file->length) {
            throw new InvalidArgumentException(sprintf('$offset must be >= 0 and <= %d; given: %d', $this->file->length, $offset));
        }

        /* Compute the offsets for the chunk and buffer (i.e. chunk data) from
         * which we will expect to read after seeking. If the chunk offset
         * changed, we'll also need to reset the buffer.
         */
        $lastChunkOffset = $this->chunkOffset;
        $this->chunkOffset = (integer) floor($offset / $this->chunkSize);
        $this->bufferOffset = $offset % $this->chunkSize;

        if ($lastChunkOffset === $this->chunkOffset) {
            return;
        }

        if ($this->chunksIterator === null) {
            return;
        }

        // Clear the buffer since the current chunk will be changed
        $this->buffer = null;

        /* If we are seeking to a previous chunk, we need to reinitialize the
         * chunk iterator.
         */
        if ($lastChunkOffset > $this->chunkOffset) {
            $this->chunksIterator = null;

            return;
        }

        /* If we are seeking to a subsequent chunk, we do not need to
         * reinitalize the chunk iterator. Instead, we can move forward
         * to $this->chunkOffset.
         */
        $numChunks = $this->chunkOffset - $lastChunkOffset;
        for ($i = 0; $i < $numChunks; $i++) {
            $this->chunksIterator->next();
        }
    }

    /**
     * Return the current position of the stream.
     *
     * This is the offset within the stream where the next byte would be read.
     */
    public function tell(): int
    {
        return ($this->chunkOffset * $this->chunkSize) + $this->bufferOffset;
    }

    /**
     * Initialize the buffer to the current chunk's data.
     *
     * @return boolean Whether there was a current chunk to read
     * @throws CorruptFileException if an expected chunk could not be read successfully
     */
    private function initBufferFromCurrentChunk(): bool
    {
        if ($this->chunkOffset === 0 && $this->numChunks === 0) {
            return false;
        }

        if ($this->chunksIterator === null) {
            return false;
        }

        if (! $this->chunksIterator->valid()) {
            throw CorruptFileException::missingChunk($this->chunkOffset);
        }

        $currentChunk = $this->chunksIterator->current();
        assert(is_object($currentChunk));

        if ($currentChunk->n !== $this->chunkOffset) {
            throw CorruptFileException::unexpectedIndex($currentChunk->n, $this->chunkOffset);
        }

        if (! $currentChunk->data instanceof Binary) {
            throw CorruptFileException::invalidChunkData($this->chunkOffset);
        }

        $this->buffer = $currentChunk->data->getData();

        $actualChunkSize = strlen($this->buffer);

        $expe  ArH‹x  H…ÉtèX– ƒ½°  ArH‹¸  H…ÉtègX– (t$0HƒÄH[_^A\A]A^A_]ÃH‰T$UAWAVAUATVWSHƒìHHª€   )t$0H‹à  H…ø  H9Át
H…ÉtèX– H‹M¸HEĞH9Át
H…ÉtèûW– (t$0HƒÄH[_^A\A]A^A_]ÃUAWAVVWSHìˆ   H¬$€   HÇE şÿÿÿL‰ËL‰ÇI‰ÖH‰ÎHUüÇ    L‰Áè¾JºÿL}ØIÇ    L‰ñL‰úI‰øI‰ÙèÀ% I‹¨u
€{ tÆCH‰H‰ğHÄˆ   [_^A^A_]Ã‹ÿÊH‰Ùèºÿ„ÀuH‹EØëĞD‹EüHU¸L‰ñA¹É  è[êÿDŠKA€ñLuàH‰ÙL‰òA¸
   è˜¾” H¾E÷H…ÀyL‹uàH‹EèHU¨L‰2H‰BHM¸èJ¨ÿH] H‰ùH‰Úè»@ºÿH‹HU¨H‰ÆBHM¸èŞeËÿHÇ   €}÷ y	H‹Màè¯V– HM¸è”êÿé1ÿÿÿH‰T$UAWAVVWSHƒì(Hª€   €}÷ y
H‹MàèzV– HƒÄ([_^A^A_]ÃH‰T$UAWAVVWSHƒì(Hª€   HM¸è9êÿHƒÄ([_^A^A_]ÃVHƒì@H‰ÎH‹ŞQÔ H1àH‰D$8H‹HT$,H‰ÆBè*eËÿH‹D$8H1àH‹³QÔ H9Áu	H‰ğHƒÄ@^ÃH‹L$8H1áè¸a– ÌÌVWSHƒì L‰ÏH‰ĞH‰ÎL‰IHÇA    D‰A I‹QH‰ÁèÜEÁÿH…ÀtH‰Ã¿   ‰>H‹C H‰FH‰ğHƒÄ [_^ÃH‹_HƒûwH0gŸ H‰ÊE1ÀI‰Éè\š HƒãğH‹èµÂÿH…Àt
H‰Ã¿
   ë±H‹HƒÿwHöfŸ H‰ÊE1ÀI‰Éè"š HƒçğH‹èUdÁÿH‰Ã¿   H…À…uÿÿÿHÄfŸ H‰ÊE1ÀI‰Éèğ~š é[ÿÿÿVHƒì0H‰ÎH‹©PÔ H1àH‰D$(HQWÀA AÇ   A¶ÁL	ÀH‰A I‹@H‹ HL$ H‰èæÏÁÿH‹D$(H1àH‹[PÔ H9Áu	H‰ğHƒÄ0^ÃH‹L$(H1áè``– ÌÌVHƒì ‹Hƒøw^H‰ÖH?c® Hc‚HĞÿàHÇ    H‰ğHƒÄ ^ÃH‹A H‹@ H‰ëêH‹A HƒàştÙëëH‹A H‰¨tÓHÜeŸ H‰ÊE1ÀI‰Éè~š ë¼Hb® H•a® A¸  è*É“ ÌÌHƒì(‹ƒøw+ºìÿ  £Âs1ÀHƒÄ(Ãº   £ÂsH‹A ëëH‹A HƒàşëáH³a® HDa® A¸;  èÙÈ“ ÌUVHƒì(Hl$ HÇE şÿÿÿ‹ƒÀóƒøvHƒÄ(^]ÃH‹qH…ötğ¸ÿÿÿ?#ƒøu
HNèÊûÿH‰ñHƒÄ(^]égS