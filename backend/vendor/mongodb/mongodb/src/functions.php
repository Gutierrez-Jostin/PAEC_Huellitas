<?php
/*
 * Copyright 2015-present MongoDB, Inc.
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

namespace MongoDB;

use Exception;
use MongoDB\BSON\Document;
use MongoDB\BSON\PackedArray;
use MongoDB\BSON\Serializable;
use MongoDB\Builder\Type\StageInterface;
use MongoDB\Driver\Exception\RuntimeException as DriverRuntimeException;
use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\Server;
use MongoDB\Driver\Session;
use MongoDB\Driver\WriteConcern;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Exception\RuntimeException;
use MongoDB\Operation\ListCollections;
use MongoDB\Operation\WithTransaction;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use stdClass;

use function array_is_list;
use function array_key_first;
use function assert;
use function end;
use function get_object_vars;
use function is_array;
use function is_object;
use function is_string;
use function str_ends_with;
use function substr;

/**
 * Registers a PSR-3 logger to receive log messages from the driver/library.
 *
 * Calling this method again with a logger that has already been added will have
 * no effect.
 */
function add_logger(LoggerInterface $logger): void
{
    PsrLogAdapter::addLogger($logger);
}

/**
 * Unregisters a PSR-3 logger.
 *
 * Calling this method with a logger that has not been added will have no
 * effect.
 */
function remove_logger(LoggerInterface $logger): void
{
    PsrLogAdapter::removeLogger($logger);
}

/**
 * Create a new stdClass instance with the provided properties.
 * Use named arguments to specify the property names.
 *     object( property1: value1, property2: value2 )
 *
 * If property names contain a dot or a dollar characters, use array unpacking syntax.
 *     object( ...[ 'author.name' => 1, 'array.$' => 1 ] )
 *
 * @psalm-suppress MoreSpecificReturnType
 * @psalm-suppress LessSpecificReturnStatement
 */
function object(mixed ...$values): stdClass
{
    return (object) $values;
}

/**
 * Check whether all servers support executing a write stage on a secondary.
 *
 * @internal
 * @param Server[] $servers
 */
function all_servers_support_write_stage_on_secondary(array $servers): bool
{
    /* Write stages on secondaries are technically supported by FCV 4.4, but the
     * CRUD spec requires all 5.0+ servers since FCV is not tracked by SDAM. */
    static $wireVersionForWriteStageOnSecondary = 13;

    foreach ($servers as $server) {
        // We can assume that load balancers only front 5.0+ servers
        if ($server->getType() === Server::TYPE_LOAD_BALANCER) {
            continue;
        }

        if (! server_supports_feature($server, $wireVersionForWriteStageOnSecondary)) {
            return false;
        }
    }

    return true;
}

/**
 * Applies a type map to a document.
 *
 * This function is used by operations where it is not possible to apply a type
 * map to the cursor directly because the root document is a command response
 * (e.g. findAndModify).
 *
 * @internal
 * @param array|object $document Document to which the type map will be applied
 * @param array        $typeMap  Type map for BSON deserialization.
 * @throws InvalidArgumentException
 */
function apply_type_map_to_document(array|object $document, array $typeMap): array|object
{
    if (! is_document($document)) {
        throw InvalidArgumentException::expectedDocumentType('$document', $document);
    }

    return Document::fromPHP($document)->toPHP($typeMap);
}

/**
 * Converts a document parameter to an array.
 *
 * This is used to facilitate unified access to document fields. It also handles
 * Document, PackedArray, and Serializable objects.
 *
 * This function is not used for type checking. Therefore, it does not reject
 * PackedArray objects or Serializable::bsonSerialize() return values that would
 * encode as BSON arrays.
 *
 * @internal
 * @throws InvalidArgumentException if $document is not an array or object
 */
function document_to_array(array|object $document): array
{
    if ($document instanceof Document || $document instanceof PackedArray) {
        /* Nested documents and arrays are intentionally left as BSON. We avoid
         * iterator_to_array() since Document and PackedArray iteration returns
         * all values as MongoDB\BSON\Value instances. */

        /** @psalm-var array */
        return $document->toPHP([
            'array' => 'bson',
            'document' => 'bson',
            'root' => 'array',
        ]);
    } elseif ($document instanceof Serializable) {
        $document = $document->bsonSerialize();
    }

    if (is_object($document)) {
        /* Note: this omits all uninitialized properties, whereas BSON encoding
         * includes untyped, uninitialized properties. This is acceptable given
         * document_to_array()'s use cases. */
        $document = get_object_vars($document);
    }

    return $document;
}

/**
 * Return a collection's encryptedFields from the encryptedFieldsMap
 * autoEncryption driver option (if available).
 *
 * @internal
 * @see https://github.com/mongodb/specifications/blob/master/source/client-side-encryption/client-side-encryption.rst#collection-encryptedfields-lookup-getencryptedfields
 * @see Collection::drop()
 * @see Database::createCollection()
 * @see Database::dropCollection()
 */
function get_encrypted_fields_from_driver(string $databaseName, string $collectionName, Manager $manager): array|object|null
{
    $encryptedFieldsMap = (array) $manager->getEncryptedFieldsMap();

    return $encryptedFieldsMap[$databaseName . '.' . $collectionName] ?? null;
}

/**
 * Return a collection's encryptedFields option from the server (if any).
 *
 * @internal
 * @see https://github.com/mongodb/specifications/blob/master/source/client-side-encryption/client-side-encryption.rst#collection-encryptedfields-lookup-getencryptedfields
 * @see Collection::drop()
 * @see Database::dropCollection()
 */
function get_encrypted_fields_from_server(string $databaseName, string $collectionName, Manager $manager, Server $server): array|object|null
{
    // No-op if the encryptedFieldsMap autoEncryption driver option was omitted
    if ($manager->getEncryptedFieldsMap() === null) {
        return null;
    }

    $collectionInfoIterator = (new ListCollections($databaseName, ['filter' => ['name' => $collectionName]]))->execute($server);

    foreach ($collectionInfoIterator as $collectionInfo) {
        /* Note: ListCollections applies a typeMap that converts BSON documents
         * to PHP arrays. This should not be problematic as encryptedFields here
         * is only used by drop helpers to obtain names of supporting encryption
         * collections. */
        return $collectionInfo['options']['encryptedFields'] ?? null;
    }

    return null;
}

/**
 * Returns whether a given value is a valid document.
 *
 * This method returns true for any array or object, but specifically excludes
 * BSON PackedArray instances
 *
 * @internal
 */
function is_document(mixed $document): bool
{
    return is_array($document) || (is_object($document) && ! $document instanceof PackedArray);
}

/**
 * Return whether the first key in the document starts with a "$" character.
 *
 * This is used for validating aggregation pipeline stages and differentiating
 * update and replacement documents. Since true and false return values may be
 * expected in different contexts, this function intentionally throws if
 * $document has an unexpected type instead of returning false.
 *
 * @internal
 * @throws InvalidArgumentException if $document is not an array or object
 */
function is_first_key_operator(array|object $document): bool
{
    if ($document instanceof PackedArray) {
        return false;
    }

    $document = document_to_array($document);

    $firstKey = array_key_first($document);

    if (! is_string($firstKey)) {
        return false;
    }

    return '$' === ($firstKey[0] ?? null);
}

/**
 * Returns whether the argument is a valid aggregation or update pipeline.
 *
 * This is primarily used for validating arguments for update and replace
 * operations, but can also be used for validating an aggregation pipeline.
 *
 * The $allowEmpty parameter can be used to control whether an empty array
 * should be considered a valid pipeline. Empty arrays are generally valid for
 * an aggregation pipeline, but the things are more complicated for update
 * pipelines.
 *
 * Update operations must prohibit empty pipelines, since libmongoc may encode
 * an empty pipeline array as an empty replacement document when writing an
 * update command (arrays and documents have the same bson_t representation).
 * For consistency, findOneAndUpdate should also prohibit empty pipelines.
 * Replace operations (e.g. replaceOne, findOneAndReplace) should reject empty
 * and non-empty pipelines alike, since neither is a replacement document.
 *
 * Note: this method may propagate an InvalidArgumentException from
 * document_or_array() if a Serializable object within the pipeline array
 * returns a non-array, non-object value from its bsonSerialize() method.
 *
 * @internal
 * @throws InvalidArgumentException
 */
function is_pipeline(array|object $pipeline, bool $allowEmpty = false): bool
{
    if ($pipeline instanceof PackedArray) {
        /* Nested documents and arrays are intentionally left as BSON. We avoid
         * iterator_to_array() since PackedArray iteration returns all values as
         * MongoDB\BSON\Value instances. */
        /** @psalm-var array */
        $pipeline = $pipeline->toPHP([
            'array' => 'bson',
            'document' => 'bson',
            'root' => 'array',
        ]);
    } elseif ($pipeline instanceof Serializable) {
        $pipeline = $pipeline->bsonSerialize();
    }

    if (! is_array($pipeline)) {
        return false;
    }

    if ($pipeline === []) {
        return $allowEmpty;
    }

    if (! array_is_list($pipeline)) {
        return false;
    }

    foreach ($pipeline as $stage) {
        if (! is_document($stage)) {
            return false;
        }

        if (! is_first_key_operator($stage)) {
            return false;
        }
    }

    return true;
}

/**
 * Returns whether the argument is a list that contains at least one
 * {@see StageInterface} object.
 *
 * @internal
 */
function is_builder_pipeline(array $pipeline): bool
{
    if (! $pipeline || ! array_is_list($pipeline)) {
        return false;
    }

    foreach ($pipeline as $stage) {
        if (is_object($stage) && $stage instanceof StageInterface) {
            return true;
        }
    }

    return false;
}

/**
 * Returns whether we are currently in a transaction.
 *
 * @internal
 * @param array $options Command options
 */
function is_in_transaction(array $options): bool
{
    if (isset($options['session']) && $options['session'] instanceof Session && $options['session']->isInTransaction()) {
        return true;
    }

    return false;
}

/**
 * Return whether the aggregation pipeline ends with an $out or $merge operator.
 *
 * This is used for determining whether the aggregation pipeline must be
 * executed against a primary server.
 *
 * @internal
 * @param array $pipeline Aggregation pipeline
 */
function is_last_pipeline_operator_write(array $pipeline): bool
{
    $lastOp = end($pipeline);

    if ($lastOp === false) {
        return false;
    }

    if (! is_array($la¬¤Æ¬¥¬¨Þ¬©¼¬­¬³ƒ¬¶¬¾e¬¿¹¬Çé¬ÈT¬ÊÓ¬ÐŠ¬Öu¬Ùã¬ÛŒ¬Ý¬Þ|¬Þž¬à¬àò¬éJ¬é÷¬óL¬öÌ¬ü­­­¾­Ï­^­:­­´­ª­h­¸­^­š­9­\­n­ Î­#U­1ª­2å­=¡­CÏ­E@­H:­I{­K²­N"­Pâ­WÖ­Xq­cž­cë­eM­gb­g÷­mƒ­o±­rÕ­wè­yÄ­}­~D­„‰­…­†N­Š?­Š•­Šµ­Šô­Œò­Žr­Ÿ­å­–›­— ­—.­—Ÿ­˜­™$­™Å­œf­­žK­ -­£Ù­¥1­¨­¬ì­¯ì­°5­±­³o­´%­´m­¶4­¶÷­¹˜­º]­¾æ­ÁW­Â×­ÃÌ­Ç†­Ê^­Ë*­Þy­Þ‘­ßy­â˜­äö­êy­î­ð¶­òb­òÓ­û£­ý›®Q®®A®4®@®ò®M®\®+®K®z®‚® …® ”®%c®(7®)ï®1'®<†®=ë®>¼®A‹®Dc®F®Vi®WÃ®Xl®Xù®YÍ®\®^À®a%®c.®c˜®dE®hi®hÝ®n®{®|9®€Å®†Ý®Ž®Ž*®$®‘R®‘²®–†®ž®¥)®¶®¶’®½é®Âà®Ð®Õÿ®Ü¨®áH®âq®ât®æm®é
®ê&®ñ®ñÑ®÷—®ü®þÈ®ÿ‘¯¯"¯ä¯"¯#ü¯$^¯$ç¯%ï¯(¯/j¯/î¯0÷¯1Ñ¯4V¯4Ó¯7¶¯8¥¯=¯>È¯?9¯A×¯D.¯F|¯I\¯Lõ¯Mˆ¯S¯\¯^Ô¯^Ü¯`§¯cm¯c£¯dl¯e­¯eø¯hŒ¯l¯oš¯oé¯w&¯y¯z¯~ö¯p¯‚e¯‹]¯’Â¯“u¯•ï¯–Ë¯—J¯(¯žÈ¯¤ ¯¤«¯ªÌ¯¬
¯°2¯±!¯±Ä¯³À¯´R¯¶¬¯¼d¯Ôä¯ßC¯à¤¯á±¯ã-¯çW¯éÐ°°–°q°°¿°ï°.Ý°=~°@.°AÃ°C½°Fí°H°I°J9°JX°Uý°V<°Z°\÷°^M°_ÿ°aÝ°b(°bÚ°f©°g±°j¾°s{°xT°y °|?°€ó°°·°‚°ƒ'°„ °ŽÃ°‘°’Û°•7°•9°˜h°›¢°œ‘° °£&°£w°§½°«m°¯ý°²õ°¸°¸ü°º‡°½i°¾º°ÀÍ°Â©°ÃŸ°Å°É;°Ë°Ëð°Í%°ÍÇ°ÐÑ°ÖK°Ù¶°Ûö°ÜÁ°ÞŒ°ßÎ°ä|°çÖ°é°ì2°ð’°õB°øç°ý°ÿ0±}±¤±É±
Ø±Ÿ±ê±s±8±t±™±ú±!±!X±)»±)ô±+±,ƒ±4Ï±4÷±7ó±9±9°±9Ü±;F±;j±<l±=q±> ±A±EF±Jj±M=±QÃ±RK±R_±X¸±Yø±Z]±[ ±\±\z±a$±d†±k•±ln±lv±mì±o±q£±s„±yá±zÊ±|÷±„F±…¢±±À±˜·±šA±šÕ±œ±Ÿ`± ±¡{±¥I±¦e±§9±©±ªë±¬±­¿±®U±±“±³.±³ÿ±»Û±È{±Ê*±Ì±ÌL±ÏV±×Î±ØR±ßo±ê±ìŽ±ï¬±ò±õ.±ûÆ²%²
~²²©²Þ²b²'‘²*Ç²+â²,²-Þ²/Q²4Ø²6Ã²=‘²B<²I4²S3²U#²X²X/²d²m²o0²oê²r»²th²2²Õ²ŽÐ²“»²—¨²™›²œ‹²¢²¢Å²¤;²©J²«ý²¬w²­a²µÍ²»h²À'²Àö²Ãà²Ó˜²Ô‹²Ú²Ú-²ÛÝ²âñ²ã²ë~²ò	²ô²ôy²õ>²÷²÷2²ûÖ³	³³
³þ³ç³¶³A³é³#Y³#c³+³0ï³1P³4Æ³6’³>·³@ç³A¿³C?³ES³Q#³S³UA³Wo³Z³[³a—³d^³dw³i³³jq³kw³kŽ³q{³x³{­³{ó³}î³Ä³–Ž³™‚³™à³š6³Ë³ Ú³£É³¦u³©n³«ù³¬³¬~³±ä³²!³¶³·A³·Ô³¸O³¹+³¼j³¿ü³Å/³ÅÀ³É9³Ë•³ÌV³Ì³³ÍÊ³ÎS³Îm³ÐF³ØQ³Ù
³Úí³Ý¼³èƒ³è¹³éß³ê³í½³î­³ï…³ô ³õ’³÷¹³ÿp´´æ´ì´s´¡´«´´}´=´´ ´%æ´)´/±´3ô´9Ä´:´´;=´;›´=¦´?¨´@Ü´D`´In´IÐ´Kb´QÎ´U§´W1´ZÊ´\&´]M´bå´eã´jQ´jx´j|´m]´o;´p™´r¹´sË´x|´}]´Í´„R´‰m´ŠR´‹µ´I´é´•¼´—)´›Á´ž¢´žÂ´¢d´©Ó´­î´¯¶´²´¸Â´½]´Àæ´Ãú´Åº´ÈË´Éñ´Ì-´Ìt´Ò¦´Ó´´Ù}´Ûó´à´ç«´é§´éî´ì!´î0´ô+´ùð´ú´ýµ áµµËµ¶µµµ¹µoµlµ¢µCµ!†µ$Ñµ'µ(µ*Eµ,sµ-Ÿµ3¶µ5rµ6µ6Üµ9šµ<óµ=Mµ@fµA+µFòµG§µGøµKÌµKÖµYîµZQµ^jµ_µ_µ_àµ_ýµd<µeÈµgõµiBµq µ} µ}×µCµäµ†=µŒ5µÖµ’–µ”µœHµœ˜µŸ=µ¡×µ¢Mµ¢›µ£óµ¤ñµ¥§µ«¢µ®µ¯'µ³µÂµµÃ,µÊNµÌ‹µÑùµØ;µÙJµÛíµÞ´µÞìµâZµãµãAµæXµæÌµêµêÃµðÀµóxµöªµùµù+µú£µû¦µüdµým¶ ¹¶Ñ¶D¶A¶Ø¶	Æ¶É¶ž¶`¶ ¶!¶$h¶%Ä¶&d¶+¶,‡¶4¶@c¶HQ¶JJ¶L­¶X¶[‹¶]j¶]o¶_Š¶_§¶dy¶gh¶h¶i^¶r\¶zQ¶}Ù¶†i¶‡¶‹³¶‹¶¶‹ß¶ŽI¶f¶’¶’>¶”;¶•¤¶œÔ¶¢²¶³Ð¶´¶´0¶µÑ¶º¶¼g¶¼x¶¾p¶Àt¶Äl¶ÄÖ¶Êb¶ÎÐ¶ØÑ¶Þû¶àà¶é<¶é²¶îB¶î^¶î÷¶òm¶öG¶ü@··R·6·S·2·Ö·)·-·2ñ·5õ·6Y·6¾·6þ·7ô·8O·8Ÿ·8 ·?ü·C‘·C³·DS·EÜ·FÊ·G·Hb·M´·Na·Qõ·Sú·Ut·Wó·bà·c›·d^·fô·gµ·iÍ·mÖ·nŽ·r:·t`·tp·u.·xß··‚p·ˆG·‹8·¦·’ó·›¤·›û·œ)·Ÿú·¢’·¢Å·¤ó·¨õ·¨û·ªF·­Æ·°·³j·¸·¸(·¼ƒ·¿·¿¯·Æl·Æÿ·É„·Êé·Ë-·Ï·ÓÆ·×µ·Þl·à÷·àù·âW·âû·ã0·ã·ã—·è5·ì;·í·îø·ð·ðò·ó’·óÆ·÷·ú`·üÖ·þr¸?¸˜¸?¸_¸‡¸‹¸¸G¸ ‡¸%Ð¸(§¸/@¸5ô¸6»¸7¸7¬¸:ð¸IV¸LÊ¸Qp¸V¸^Y¸_$¸au¸cS¸hú¸i9¸k	¸p¸sé¸t}¸{“¸| ¸…™¸‹¬¸‡¸Ž¸Í¸¬¸’_¸”Ò¸š¸š]¸¡ì¸¢º¸£-¸¥¸¦ú¸§%¸¨t¸¨¸­¸¯¸°¨¸·j¸¸Ë¸½÷¸¾U¸Ãv¸ÄF¸Çu¸Ëê¸Îd¸Îu¸Ïê¸Ó¸Õ²¸Ùm¸ÞÙ¸â¦¸ãd¸åÁ¸ç¦¸é¸é¦¸ëB¸÷[¸ü]¹ €¹5¹	*¹C¹Ð¹å¹¹¹ Î¹!¹&å¹'¶¹(Ž¹)Ü¹1R¹4x¹7?¹8¼¹>°¹?c¹C¹E ¹GÐ¹Jo¹OT¹UÑ¹V2¹YØ¹\©¹^d¹_¹a ¹e¼¹f?¹h#¹mþ¹ny¹ri¹t¹u²¹wA¹zY¹zÂ¹‚…¹8¹ô¹Žr¹’Ù¹”¨¹¢†¹¦3¹¨Y¹°¹°¢¹³X¹´¹·¹¸q¹¾Ö¹ÍÐ¹ÎÊ¹Úˆ¹Ý‹¹ßŠ¹äC¹å¹êž¹í›¹ö	¹÷è¹÷îº7º^º,º€º:ººÁº#ºÊººtº!áº$üº&Uº+Oº.º/zº1º3lº5zº9aºEÍºJºLEºX#º[…ºa…ºaËºbUºfæºgRºk ºu}ºxLºykºz`º~ôº†º‰¶ºŠáº‹ÑºŒ‘ºŽïº‘º‘eº’¦º“º“8º”º›7ºœtº©Uº­êº®oº®Åº®Ùº´…ºµ0ººÔº¿ºÂ—ºÃÎºÄÈºÌuºÏŽºçdºéRºðjºñËºó-ºôFºõtº÷¶»y»Î»Ó»¿»Ò»3»Ó»E»Ø»×»º»/'»/Ž»0®»1U»6=»;q»;§»<„»As»AÇ»JÞ»L »Oƒ»W+»XR»Xv»Y?»]»^N»a”»aÓ»d”»eg»hê»o£»r»sÛ»ul»xÄ»zÝ»|Ð»„K»„|»ˆ»“<»™8»šR»¤Ö»§{»ª»®V»®µ»®Ö»¼"»Á »Âb»Åù»ÉÃ»Ï,»ÏÕ»ÑÄ»Ó»Ó”»ÕÜ»ÙE»Ú“»á\»æ»æ<»ç»çÂ»è<»é,»é„»ì»í-»ñ”¼ ¸¼¡¼V¼¼!¼y¼ä¼
Ï¼¢¼¿¼€¼x¼I¼²¼"ƒ¼"¶¼,²¼-÷¼.È¼1A¼6m¼;8¼<á¼=¼=Þ¼=ì¼Ms¼Pâ¼R{¼R«¼X¼Zz¼_‘¼bÛ¼e	¼h¼iM¼j¼kx¼n¼qÕ¼r+¼tC¼u¾¼y;¼‡Ã¼]¼—c¼’¼ ¼ £¼¡Î¼£Ò¼¥¼©D¼«¼²¼·Y¼¸7¼¸|¼¸°¼¹´¼½¶¼À¯¼Âá¼Ã0¼ÃÏ¼ÇÍ¼É(¼Ê3¼Ì¼Î¸¼Ò"¼Óù¼Ö¼×¼×Ç¼Üß¼ßÁ¼â!¼æ5¼ê)¼ê6¼ë¹¼í¼î¸¼ò¢¼õx¼õ¡¼øÎ¼üÇ¼ÿß½&½ð½Æ½]½)½P½½½'"½-¸½3½4$½5L½9½@¢½C½D½I»½N‡½Q9½Xà½^r½_„½a•½g(½hÿ½u.½v`½wý½{½|X½;½„~½…s½†ã½‹Ý½õ½Ž*½½‘»½“Ñ½—u½˜J½›ú½¡½¡W½¢Ù½¨½¨Ò½©†½©ä½ª«½«O½¯½±—½±Ä½²Ê½¶P½¹¡½¼"½¾X½ÍÖ½Ïƒ½Ý½á,½ás½æJ½ëI½ò½ó‡½ô½÷D½þ’½ÿX¾ °¾›¾g¾™¾Ù¾Ð¾‡¾¾"¾(@¾)J¾* ¾*‹¾*À¾-µ¾/A¾/–¾0­¾6l¾8ô¾9”¾:l¾;è¾Aá¾Cû¾Dž¾Hž¾M¾Qf¾Vœ¾\2¾\½¾a¾cï¾eP¾f
¾j]¾k
¾nø¾sh¾så¾u¥¾w ¾{¾{µ¾{¿¾}v¾~¾×¾€`¾‚"¾ƒ4¾„N¾…~¾‡'¾‘ò¾’ç¾“2¾“å¾”v¾–¾—a¾˜|¾šü¾V¾žˆ¾ ù¾¡1¾¡È¾£ç¾¦f¾¨B¾ª9¾«Ð¾¬»¾­K¾¯r¾¯È¾´y¾·Œ¾º>¾ºv¾¿¾¾ÀÃ¾Â6¾ÄV¾Ê‘¾Ñ¾×ß¾ÙV¾Ý§¾ç¾çN¾ç²¾èO¾ë¤¾ï%¾ñ¾õ¾öÃ¾ø/¾ø“¾ùK¿
W¿‰¿d¿“¿K¿Ž¿*¿*¿–¿Æ¿#y¿#º¿)†¿)î¿*R¿,õ¿,ø¿/e¿0m¿4L¿8¿9º¿CÈ¿G¿G¿G¯¿OÆ¿P¿P¡¿R¿SZ¿U¼¿X»¿`Î¿bÖ¿l6¿l³¿oM¿}¿€õ¿„<¿‡¿¿‘ˆ¿’X¿”ß¿•²¿—æ¿—ë¿ž¿ T¿¡´¿¢e¿¤L¿«±¿´¿¹c¿¼N¿Åv¿ÆÂ¿ÆÕ¿Ë±¿Í¿ÎÕ¿ÐÂ¿Òg¿Ò˜¿ÒÝ¿Ó¢¿Õˆ¿Ö‘¿ÛK¿äU¿í´¿ù%À ¦À@ÀÑÀSÀóÀÀ
ÉÀ¾ÀŠÀ_ÀŒÀÀ§À$ŒÀ&õÀ'¸À)•À+¾À.À.ÅÀ/³À9ûÀ=À?ÀGÀTÀUõÀUúÀZóÀ\5À` Àa¥ÀcÀgmÀhÍÀlUÀoÖÀt®ÀwÀxÀxƒÀ}ÊÀ„LÀ„ŸÀ„ËÀ†áÀŽšÀoÀ‘ªÀ’tÀ—2À—eÀ—ÄÀŸÀŸåÀ¡=À¡½À£ºÀ¥¢À¨aÀ©MÀª<À¬mÀ°ŒÀ¶£ÀºÀ»ÀÀ½uÀ¿IÀÀˆÀÅºÀÍÀÍšÀÎ!ÀÎŽÀÎ‘ÀÐÚÀÒVÀÖ}ÀÛõÀÝÀÝžÀÞ<ÀÞCÀß"Àá½ÀâíÀèõÀîWÀîÉÀð°ÀòÀôªÀôèÀõbÀõdÀú©ÀüÀý%Àþ¥ÁªÁnÁÁ¦ÁÛÁKÁÁÁÁ!ƒÁ"2Á+Á/Á2‚Á4éÁ5OÁ5·Á=<ÁBRÁH7ÁHeÁKLÁLOÁL¥ÁO¿ÁPæÁR	ÁR!ÁZÄÁ\¹ÁhÎÁkÁvÕÁ€ßÁ€ñÁâÁ„Á‹#ÁÜÁ“ìÁ”æÁ–ûÁž“Á¥Á§çÁ«+Á«jÁ­Á¯³Á·#Á·nÁ¸hÁ»Á½ØÁÈÁÍÁÍ‡ÁÎzÁÐMÁÐëÁÔõÁ×Á×4Á×ÁÞ4Áà‘ÁæÁæ¥ÁçùÁìÁïaÁïyÁóZÁó›ÁúnÁúºÁûŒÁþ¿Áÿ_ÂÂzÂÂ*ÂÚÂuÂÃÂãÂÂÂ!ZÂ!yÂ"HÂ,oÂ.ÕÂ2±Â4YÂ6,Â6•Â:FÂ;¥ÂB)ÂFãÂQðÂSKÂU@ÂV‡ÂWîÂb’ÂeWÂj™ÂjðÂm4ÂpºÂmÂ€[Â€ÕÂ€áÂ‡„ÂˆÂˆxÂ‰ÆÂ…Â‘bÂ’bÂ–Â–Â—åÂ˜ÛÂ™&Â¡žÂ¤ÞÂ¥1Â¥©Â«#Â±]Â³Â³^Â¶HÂ¶ùÂ¸ßÂ»%Â¾0ÂÃâÂÄ©ÂÆpÂÆÔÂÎiÂÏßÂ×˜ÂÙ½ÂÞvÂß1ÂâsÂãÖÂé¿Âê4ÂëÂó†ÂõåÂöÔÂ÷6ÂùáÂú`Âý‚ÃÃ½ÃRÃ°ÃPÃmÃ?Ã ¢Ã"˜Ã-÷Ã1Ã7ŸÃ9öÃ<4ÃD€ÃKÃMXÃOèÃSÄÃ[Ã]UÃ^NÃ`oÃi°ÃkÃlöÃrUÃ{Ã|RÃ€©Ã€íÃ‚Ã‹Ã‹ÍÃ“Ã™=Ã›NÃžnÃ¥dÃ©ªÃ«¢Ã¬úÃ°Ã±bÃ²Ã´tÃ¶9Ã·”Ã¸ïÃ½`ÃÂXÃÂYÃÂòÃÇBÃÈÃÈÃÏ.ÃÐÃÑÃÒHÃÓÏÃ×bÃê>Ãí›ÃïÃòÉÃúzÃûbÃýÃÿÍÄÄVÄÄ¶Ä§ÄFÄÃÄ•ÄXÄÄÇÄ¢ÄuÄŠÄçÄ!ÏÄ&€Ä'%Ä+ÿÄ,/Ä.LÄ0©Ä1QÄ3ÞÄ5bÄ@öÄC ÄFuÄGóÄLÂÄMÁÄQ,ÄQ«ÄU ÄVÄW¡ÄXƒÄ\íÄ^îÄiPÄiçÄlðÄwCÄwÐÄxúÄyÉÄzlÄ}‚Ä	Ä‚$Ä‹\ÄŒ+ÄŽ_Ä—?Ä™ðÄ™òÄž^Ä¯5Ä¯xÄ´&Ä½‡Ä½ÐÄ¾=Ä¾[ÄÁ8ÄÅùÄÆÄÇàÄË·ÄÎ¯ÄÒ=ÄÕŽÄÕ°ÄÖcÄÚjÄÞnÄßqÄåÄñ«Äô=ÄôaÄôÄ÷´ÄûÐÄü–ÄýÅ TÅPÅ	ÅÖÅ	oÅ|Å[ÅþÅ¯ÅÅ³ÅÙÅ"vÅ$ÈÅ+wÅ2|Å6MÅ<*Å<0Å<uÅA‘ÅGÅG³ÅQ-ÅS^ÅT¬ÅTàÅVÏÅ^Å_,Åh‰ÅkéÅmxÅnÞÅq§ÅsÎÅtcÅt…Å{vÅ~¤Å Å‹¬ÅØÅ‘=Åœ€Å¢4Å©ßÅª²Å´ÿÅ¶!Å¸-Å¸LÅ»8Å¼ÌÅ½¡Å¾ÔÅÄ·ÅÇËÅÈTÅÈÅÍ<ÅÎ8ÅÐÅÅÐÌÅÐðÅÑêÅÚ8ÅßëÅâÁÅåÅî˜ÅøÕÆ¼ÆØÆCÆ	±Æ	òÆOÆºÆDÆÆ¹Æ&íÆ'CÆ(6Æ*=Æ+ÙÆ,èÆ1eÆ2'Æ3¸Æ6LÆ: Æ@åÆD$ÆEÆHÆMÆYTÆZXÆ[ØÆ\Æ\³Æ`«Æd'ÆlbÆmèÆp÷ÆqƒÆw’Æ|úÆ²ÆƒhÆ„ÍÆˆ”Æˆ¼ÆˆØÆ‰EÆ‰bÆ‹ÆŒ ÆŒÆ~ÆšéÆž×Æ¤_Æ¤ÊÆ¤ïÆ§3Æ«ãÆ¬	Æ®éÆ¯…Æ°ŸÆµµÆ¸ÁÆ¹Æ¼?Æ¼êÆÀÁÆÅæÆÊ1ÆÌSÆÌxÆÍÆÎ>ÆÐ%ÆÕ£ÆÙJÆÝ&ÆÝüÆß3ÆãŒÆéøÆí©Æï7ÆñÆö Æ÷|ÇhÇ~ÇÇÇûÇ"Ç$ëÇ'ñÇ07Ç7GÇ8Ç8_Ç;üÇA!ÇE+ÇQŒÇWwÇXÇZoÇZÅÇcÇföÇoœÇp ÇwÇxyÇz+Ç{ÇƒÇ…|Ç‰	Ç‹mÇŒ™ÇÇÇ’ÑÇ˜ŒÇ™DÇœ%Ç£àÇ©ÃÇª[Ç«(Ç­àÇ¯øÇ¼BÇÀžÇÌ ÇÐ´ÇÓŒÇÛmÇÜaÇßŠÇáíÇâ8Çå”ÇæjÇéÑÇêßÇì[ÇíÞÇî>ÇñxÇô}Ç÷"ÇøÈÈŸÈ<È“È»ÈáÈ_È[È#“È'¿È(ÁÈ)ÏÈ-4È1ÐÈ2È8ÏÈ=?È=@ÈCÈDÇÈHHÈL{ÈMéÈQ”ÈR	È\Èd1ÈhÝÈmFÈwCÈx[Èz£È{WÈ{ÛÈ|ÌÈ|áÈòÈÂÈƒ›ÈˆÈ“þÈ™ÎÈ1È£È¥ËÈ¥ÜÈ¨ñÈ®+È®™È³âÈ´ÒÈ¶ÌÈ·ÀÈÀ¸ÈÂéÈËØÈÚ£ÈäøÈêˆÈërÈëøÈìÈìZÈï2ÈñÞÈóZÈôEÈöÈù'ÈújÈûZÈþòÈþýÉlÉ»É¾É§ÉØÉKÉãÉvÉÉÉ,áÉ-ïÉ/•É0+É0ØÉ1PÉ2§É8ŽÉ9âÉ;0É;âÉ?îÉA¶ÉBÍÉPÉQ-ÉRÉRÆÉ[&É\¤É^#É`öÉb–ÉcäÉd±ÉeÉkÝÉm’Ép	ÉuDÉyIÉ{rÉ†¥É†ÃÉ‡®ÉŽÉÖÉ’hÉ•ÉÉžºÉ¢)É¢_É¢ºÉ¢éÉ¦ÍÉ¨sÉ®ÒÉ¯GÉ¶ˆÉº/ÉÁüÉË—ÉÌ,ÉÏšÉÏžÉÖŸÉ×?É×”ÉÞÉà¥Éì€ÉóÉôÉöÉ÷>Éþ¶ÊâÊÊ=ÊÐÊ
åÊIÊÊÉÊöÊYÊÏÊØÊ#UÊ#ÐÊ(¿Ê)yÊ/RÊ4üÊ5¯Ê7¼Ê;éÊG}ÊG‹ÊH¼ÊH¿ÊW‚ÊXÊ_¼ÊbnÊcØÊeaÊjõÊkËÊtÊu Êy™ÊyÑÊ|¸Ê†Ê‡æÊ‡ùÊ‹JÊ}Ê+Ê•ÉÊ–©Ê–¾Ê_Ê¥ÎÊ§ýÊ²:Ê¸±Ê¿fÊÀFÊÂHÊÃÊÄHÊÄõÊÉ(ÊÉWÊÊ7ÊË-ÊËFÊÌÊÍ ÊÕÊÕÄÊ×PÊßqÊßÑÊåñÊæ¥ÊçSÊç¢Êí=Ê÷FÊøTÊø¼ÊùcÊülËíË$ËiËXË¿Ë	ãË†ËïË„ËhËVË6ËþËË!Ë“ËCËË7ËYË*|Ë+XË0¨Ë3iË8Ë:˜Ë=$ËDËEÃËMËPiËUËc*ËfcËl€ËlËtƒËw9ËwcËyÒË|CË|æË}§ËoË‡-Ë‡ZË‡×Ë‰pË‹™Ë˜Ë˜[Ë˜ËßË¡KË¦~Ë«€Ë­²Ë¯¹Ë²uËµË½ËÁyËÃpËÄFËÄËÅÛËÊ0ËËJËÍ@ËÏ³ËÔ‹ËÔëËÕ¦ËÖÆËØ‘ËÙ%ËÝãËß\Ëà§Ëã¦ËçËçÜËè\ËêRËì˜Ëô„ËùBËû¡Ëÿ)Ëÿ5ÌÌ1Ì_ÌpÌÌÌ Ì&tÌ42Ì6*Ì6TÌ;“Ì<9Ì?üÌA†ÌD>ÌE¦ÌKÌN8ÌP@ÌW3Ì\
Ì]>ÌaˆÌc^ÌgÌhYÌh½ÌjpÌkäÌlPÌl„ÌréÌyAÌz^Ì}çÌhÌ…#Ì†Ì†“Ì‹gÌ‹{Ì”Ì˜ÌšãÌ¨œÌ«ÚÌ­kÌ±©Ì²VÌ·ÀÌ»MÌ¼SÌ¾¬