<?php

/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */

declare(strict_types=1);

namespace MongoDB\Builder\Query;

use DateTimeInterface;
use MongoDB\BSON\Binary;
use MongoDB\BSON\Decimal128;
use MongoDB\BSON\Document;
use MongoDB\BSON\Int64;
use MongoDB\BSON\Javascript;
use MongoDB\BSON\PackedArray;
use MongoDB\BSON\Regex;
use MongoDB\BSON\Serializable;
use MongoDB\BSON\Type;
use MongoDB\Builder\Expression\ResolvesToDouble;
use MongoDB\Builder\Type\ExpressionInterface;
use MongoDB\Builder\Type\FieldQueryInterface;
use MongoDB\Builder\Type\GeometryInterface;
use MongoDB\Builder\Type\Optional;
use MongoDB\Builder\Type\QueryInterface;
use MongoDB\Model\BSONArray;
use stdClass;

/**
 * @internal
 */
trait FactoryTrait
{
    /**
     * Matches arrays that contain all elements specified in the query.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/all/
     * @no-named-arguments
     * @param DateTimeInterface|FieldQueryInterface|Type|array|bool|float|int|null|stdClass|string ...$value
     */
    public static function all(
        DateTimeInterface|Type|FieldQueryInterface|stdClass|array|bool|float|int|null|string ...$value,
    ): AllOperator {
        return new AllOperator(...$value);
    }

    /**
     * Joins query clauses with a logical AND returns all documents that match the conditions of both clauses.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/and/
     * @no-named-arguments
     * @param QueryInterface|array ...$queries
     */
    public static function and(QueryInterface|array ...$queries): AndOperator
    {
        return new AndOperator(...$queries);
    }

    /**
     * Matches numeric or binary values in which a set of bit positions all have a value of 0.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/bitsAllClear/
     * @param BSONArray|Binary|PackedArray|array|int|string $bitmask
     */
    public static function bitsAllClear(Binary|PackedArray|BSONArray|array|int|string $bitmask): BitsAllClearOperator
    {
        return new BitsAllClearOperator($bitmask);
    }

    /**
     * Matches numeric or binary values in which a set of bit positions all have a value of 1.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/bitsAllSet/
     * @param BSONArray|Binary|PackedArray|array|int|string $bitmask
     */
    public static function bitsAllSet(Binary|PackedArray|BSONArray|array|int|string $bitmask): BitsAllSetOperator
    {
        return new BitsAllSetOperator($bitmask);
    }

    /**
     * Matches numeric or binary values in which any bit from a set of bit positions has a value of 0.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/bitsAnyClear/
     * @param BSONArray|Binary|PackedArray|array|int|string $bitmask
     */
    public static function bitsAnyClear(Binary|PackedArray|BSONArray|array|int|string $bitmask): BitsAnyClearOperator
    {
        return new BitsAnyClearOperator($bitmask);
    }

    /**
     * Matches numeric or binary values in which any bit from a set of bit positions has a value of 1.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/bitsAnySet/
     * @param BSONArray|Binary|PackedArray|array|int|string $bitmask
     */
    public static function bitsAnySet(Binary|PackedArray|BSONArray|array|int|string $bitmask): BitsAnySetOperator
    {
        return new BitsAnySetOperator($bitmask);
    }

    /**
     * Specifies a rectangular box using legacy coordinate pairs for $geoWithin queries. The 2d index supports $box.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/box/
     * @param BSONArray|PackedArray|array $value
     */
    public static function box(PackedArray|BSONArray|array $value): BoxOperator
    {
        return new BoxOperator($value);
    }

    /**
     * Specifies a circle using legacy coordinate pairs to $geoWithin queries when using planar geometry. The 2d index supports $center.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/center/
     * @param BSONArray|PackedArray|array $value
     */
    public static function center(PackedArray|BSONArray|array $value): CenterOperator
    {
        return new CenterOperator($value);
    }

    /**
     * Specifies a circle using either legacy coordinate pairs or GeoJSON format for $geoWithin queries when using spherical geometry. The 2dsphere and 2d indexes support $centerSphere.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/centerSphere/
     * @param BSONArray|PackedArray|array $value
     */
    public static function centerSphere(PackedArray|BSONArray|array $value): CenterSphereOperator
    {
        return new CenterSphereOperator($value);
    }

    /**
     * Adds a comment to a query predicate.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/comment/
     * @param string $comment
     */
    public static function comment(string $comment): CommentOperator
    {
        return new CommentOperator($comment);
    }

    /**
     * The $elemMatch operator matches documents that contain an array field with at least one element that matches all the specified query criteria.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/elemMatch/
     * @param DateTimeInterface|FieldQueryInterface|QueryInterface|Type|array|bool|float|int|null|stdClass|string $query
     */
    public static function elemMatch(
        DateTimeInterface|Type|FieldQueryInterface|QueryInterface|stdClass|array|bool|float|int|null|string $query,
    ): ElemMatchOperator {
        return new ElemMatchOperator($query);
    }

    /**
     * Matches values that are equal to a specified value.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/eq/
     * @param DateTimeInterface|Type|array|bool|float|int|null|stdClass|string $value
     */
    public static function eq(DateTimeInterface|Type|stdClass|array|bool|float|int|null|string $value): EqOperator
    {
        return new EqOperator($value);
    }

    /**
     * Matches documents that have the specified field.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/exists/
     * @param bool $exists
     */
    public static function exists(bool $exists = true): ExistsOperator
    {
        return new ExistsOperator($exists);
    }

    /**
     * Allows use of aggregation expressions within the query language.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/expr/
     * @param DateTimeInterface|ExpressionInterface|Type|array|bool|float|int|null|stdClass|string $expression
     */
    public static function expr(
        DateTimeInterface|Type|ExpressionInterface|stdClass|array|bool|float|int|null|string $expression,
    ): ExprOperator {
        return new ExprOperator($expression);
    }

    /**
     * Selects geometries that intersect with a GeoJSON geometry. The 2dsphere index supports $geoIntersects.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/geoIntersects/
     * @param Document|GeometryInterface|Serializable|array|stdClass $geometry
     */
    public static function geoIntersects(
        Document|Serializable|GeometryInterface|stdClass|array $geometry,
    ): GeoIntersectsOperator {
        return new GeoIntersectsOperator($geometry);
    }

    /**
     * Specifies a geometry in GeoJSON format to geospatial query operators.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/geometry/
     * @param string $type
     * @param BSONArray|PackedArray|array $coordinates
     * @param Optional|Document|Serializable|array|stdClass $crs
     */
    public static function geometry(
        string $type,
        PackedArray|BSONArray|array $coordinates,
        Optional|Document|Serializable|stdClass|array $crs = Optional::Undefined,
    ): GeometryOperator {
        return new GeometryOperator($type, $coordinates, $crs);
    }

    /**
     * Selects geometries within a bounding GeoJSON geometry. The 2dsphere and 2d indexes support $geoWithin.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/geoWithin/
     * @param Document|GeometryInterface|Serializable|array|stdClass $geometry
     */
    public static function geoWithin(
        Document|Serializable|GeometryInterface|stdClass|array $geometry,
    ): GeoWithinOperator {
        return new GeoWithinOperator($geometry);
    }

    /**
     * Matches values that are greater than a specified value.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/gt/
     * @param DateTimeInterface|Type|array|bool|float|int|null|stdClass|string $value
     */
    public static function gt(DateTimeInterface|Type|stdClass|array|bool|float|int|null|string $value): GtOperator
    {
        return new GtOperator($value);
    }

    /**
     * Matches values that are greater than or equal to a specified value.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/gte/
     * @param DateTimeInterface|Type|array|bool|float|int|null|stdClass|string $value
     */
    public static function gte(DateTimeInterface|Type|stdClass|array|bool|float|int|null|string $value): GteOperator
    {
        return new GteOperator($value);
    }

    /**
     * Matches any of the values specified in an array.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/in/
     * @param BSONArray|PackedArray|array $value
     */
    public static function in(PackedArray|BSONArray|array $value): InOperator
    {
        return new InOperator($value);
    }

    /**
     * Validate documents against the given JSON Schema.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/jsonSchema/
     * @param Document|Serializable|array|stdClass $schema
     */
    public static function jsonSchema(Document|Serializable|stdClass|array $schema): JsonSchemaOperator
    {
        return new JsonSchemaOperator($schema);
    }

    /**
     * Matches values that are less than a specified value.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/lt/
     * @param DateTimeInterface|Type|array|bool|float|int|null|stdClass|string $value
     */
    public static function lt(DateTimeInterface|Type|stdClass|array|bool|float|int|null|string $value): LtOperator
    {
        return new LtOperator($value);
    }

    /**
     * Matches values that are less than or equal to a specified value.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/lte/
     * @param DateTimeInterface|Type|array|bool|float|int|null|stdClass|string $value
     */
    public static function lte(DateTimeInterface|Type|stdClass|array|bool|float|int|null|string $value): LteOperator
    {
        return new LteOperator($value);
    }

    /**
     * Specifies a maximum distance to limit the results of $near and $nearSphere queries. The 2dsphere and 2d indexes support $maxDistance.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/maxDistance/
     * @param Decimal128|Int64|float|int $value
     */
    public static function maxDistance(Decimal128|Int64|float|int $value): MaxDistanceOperator
    {
        return new MaxDistanceOperator($value);
    }

    /**
     * Specifies a minimum distance to limit the results of $near and $nearSphere queries. For use with 2dsphere index only.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/minDistance/
     * @param Int64|float|int $value
     */
    public static function minDistance(Int64|float|int $value): MinDistanceOperator
    {
        return new MinDistanceOperator($value);
    }

    /**
     * Performs a modulo operation on the value of a field and selects documents with a specified result.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/mod/
     * @param Decimal128|Int64|float|int $divisor
     * @param Decimal128|Int64|float|int $remainder
     */
    public static function mod(
        Decimal128|Int64|float|int $divisor,
        Decimal128|Int64|float|int $remainder,
    ): ModOperator {
        return new ModOperator($divisor, $remainder);
    }

    /**
     * Matches all values that are not equal to a specified value.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/ne/
     * @param DateTimeInterface|Type|array|bool|float|int|null|stdClass|string $value
     */
    public static function ne(DateTimeInterface|Type|stdClass|array|bool|float|int|null|string $value): NeOperator
    {
        return new NeOperator($value);
    }

    /**
     * Returns geospatial objects in proximity to a point. Requires a geospatial index. The 2dsphere and 2d indexes support $near.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/near/
     * @param Document|GeometryInterface|Serializable|array|stdClass $geometry
     * @param Optional|Decimal128|Int64|float|int $maxDistance Distance in meters. Limits the results to those documents that are at most the specified distance from the center point.
     * @param Optional|Decimal128|Int64|float|int $minDistance Distance in meters. Limits the results to those documents that are at least the specified distance from the center point.
     */
    public static function near(
        Document|Serializable|GeometryInterface|stdClass|array $geometry,
        Optional|Decimal128|Int64|float|int $maxDistance = Optional::Undefined,
        Optional|Decimal128|Int64|float|int $minDistance = Optional::Undefined,
    ): NearOperator {
        return new NearOperator($geometry, $maxDistance, $minDistance);
    }

    /**
     * Returns geospatial objects in proximity to a point on a sphere. Requires a geospatial index. The 2dsphere and 2d indexes support $nearSphere.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/nearSphere/
     * @param Document|GeometryInterface|Serializable|array|stdClass $geometry
     * @param Optional|Decimal128|Int64|float|int $maxDistance Distance in meters.
     * @param Optional|Decimal128|Int64|float|int $minDistance Distance in meters. Limits the results to those documents that are at least the specified distance from the center point.
     */
    public static function nearSphere(
        Document|Serializable|GeometryInterface|stdClass|array $geometry,
        Optional|Decimal128|Int64|float|int $maxDistance = Optional::Undefined,
        Optional|Decimal128|Int64|float|int $minDistance = Optional::Undefined,
    ): NearSphereOperator {
        return new NearSphereOperator($geometry, $maxDistance, $minDistance);
    }

    /**
     * Matches none of the values specified in an array.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/nin/
     * @param BSONArray|PackedArray|array $value
     */
    public static function nin(PackedArray|BSONArray|array $value): NinOperator
    {
        return new NinOperator($value);
    }

    /**
     * Joins query clauses with a logical NOR returns all documents that fail to match both clauses.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/nor/
     * @no-named-arguments
     * @param QueryInterface|array ...$queries
     */
    public static function nor(QueryInterface|array ...$queries): NorOperator
    {
        return new NorOperator(...$queries);
    }

    /**
     * Inverts the effect of a query expression and returns documents that do not match the query expression.
     *
     * @see https://www.mongodb.com/docs/manual/reference/operator/query/not/
     * @param DateTimeInterface|FieldQueryInterface|Type|array|bool|float|int|null|stdClass|string $expression
     */
    public static function not(
      §ÐÙ§Ðè¿§ÐëY§Ðë‘§Ðí8§Ðò§Ðö;§ÐøR§Ðý˜§Ðÿ®§Ñ§Ñ~§Ñ™§Ññ§Ñ§Ñe§Ñ §ÑÇ§Ñ­§Ñ.
§Ñ/¾§Ñ3Ó§Ñ9§ÑG4§ÑI²§ÑK©§ÑQà§ÑRî§ÑT›§Ñb²§ÑfA§Ñff§Ñg`§Ñgw§Ñjá§Ñu§Ñ|R§Ñ}Î§ÑR§Ñ…§Ñˆ§Ñ‹+§Ñ‘±§Ñ’¹§Ñ—ç§Ñ˜§§Ñ 5§Ñ¢§Ñ¥ú§Ñ¦§Ñ¯§Ñ³ß§Ñ´º§Ñ·e§ÑºÎ§Ñ»i§Ñ¼.§Ñ¼r§Ñ¾ª§ÑÃe§ÑÅ§ÑÍ§ÑÍè§ÑÖv§ÑÙ§Ñà§Ñãü§Ñêº§Ñí§Ñï§Ñ÷Í§Ñý›§Ñýô§Ñþ¶§ÒÑ§Ò¸§Ò¾§Ò`§ÒF§ÒN§Òž§Ò|§Ò7§ÒÍ§Ò"¾§Ò$§Ò$¹§Ò'—§Ò-û§Ò2R§Ò7§Ò=/§Ò@D§ÒB%§ÒEY§ÒG§ÒI	§ÒI•§ÒM‹§ÒN¼§ÒOÎ§ÒQÆ§ÒR^§ÒVU§ÒW5§ÒWç§Ò[§Ò[§Òk§Òl¨§Òo›§Òu“§Òxº§Òz:§Ò€j§Ò…6§Ò†M§ÒŒ §Òø§Ò£§Ò•È§Ò–Â§Ò™§Ò ƒ§Ò¢6§Ò©ú§Ò¬
§Ò®^§Ò°§Ò±§Ò³ §Òµè§Ò¶Q§Ò¸§Ò¸r§Ò½ñ§ÒÅ°§ÒÌ²§ÒÑG§ÒÓ‘§ÒÔ€§ÒÖP§ÒØ„§ÒØ½§ÒÞD§ÒÞc§ÒÞä§Òà˜§Òä^§Òé¤§Òì§Òìº§ÒñV§ÒøŸ§Òÿ…§Ó §Ó^§Ó‹§Óx§Óè§Óa§Ó®§Ó£§ÓÁ§Ó|§Ó‹§Ó)§Ó”§Ó K§Ó Ý§Ó!X§Ó"O§Ó#‰§Ó&
§Ó't§Ó'{§Ó(§Ó0§Ó0ž§Ó2L§Ó>¨§ÓA,§ÓEÔ§ÓIñ§ÓP@§ÓU§ÓU¥§ÓV·§ÓW §ÓW·§ÓeQ§ÓgZ§ÓlÕ§Óvs§Óxn§Óy^§Ó{±§Ó{Ð§Ó|‘§Ó€A§Ó….§ÓZ§Óy§Ó§Óñ§Ó”k§Ó”¶§Ó•Ã§Ó–½§Ó˜›§Ó˜ §Ó™N§Ó›¯§ÓŸô§Ó Ç§Ó¡D§Ó¡¾§Ó¢§Ó¥ §Ó¦«§Ó©1§Ó¬h§Ó®â§Ó°-§Ó¸ø§Ó½›§ÓÂô§ÓÈ§ÓÊ,§ÓÏë§ÓÑ›§ÓØµ§Óã §ÓæÚ§Óí§ÓïR§Óóê§ÓùN§ÓûS§Óû§Óû·§Óü{§Ô Ÿ§ÔÈ§Ô«§Ô§Ôò§ÔÕ§Ôv§Ô!ƒ§Ô&>§Ô(§Ô(Ô§Ô*x§Ô,b§Ô-P§Ô-ð§Ô1…§Ô6Ç§Ô>§Ô>h§Ô?ù§ÔA4§ÔBï§ÔI2§ÔJz§ÔK¡§ÔL§ÔM§ÔN@§ÔPT§ÔQ§ÔQ!§ÔU¸§ÔUê§Ô_È§Ô_ï§Ô`§ÔdF§ÔeÝ§Ôiy§ÔmŽ§Ôt<§Ôuu§Ôzc§Ô{É§Ô{ø§Ô|E§Ô|s§Ô€†§Ô‚·§Ô„±§Ô9§Ô‘Þ§Ô’ƒ§Ô’¡§Ô•§Ô™Œ§Ôšb§ÔœÁ§Ô b§Ô©v§Ôª•§Ô¬§Ô­§Ô­@§Ô±M§Ô²W§Ô¸¿§ÔÂš§ÔÅÛ§ÔÇ§ÔÊ¨§ÔÎ§ÔÎE§ÔØK§ÔØc§ÔÙ3§ÔÙÚ§ÔãQ§Ôä‰§Ôï§Ôô§Ô÷Ä§Ô÷ù§Ôÿ§Õ‰§Õ	 §Õ	Ñ§ÕÏ§Õ-§Õ§Õ§Õs§Õe§Õ÷§Õû§Õ¸§Õ§Õß§Õ&9§Õ/È§Õ4ö§Õ7Ÿ§Õ9§Õ>L§Õ@Ð§ÕA¡§ÕBH§ÕF8§ÕG¡§ÕKÛ§ÕLû§ÕM§ÕMJ§ÕN=§ÕS§ÕUR§ÕUÃ§Õ\Å§Õ^š§Õ^»§Õ_o§Õ`»§Õbc§Õcî§Õdÿ§ÕjP§Õnï§Õq§Õ{&§Õ}§Õ€ð§Õ‚³§Õ‹§Õ‹e§Õ˜g§Õ™æ§Õœø§Õ¦–§Õ¯§Õ·>§Õ½§Õ½Í§Õ¾Ñ§ÕÁN§ÕÅL§ÕÅ°§ÕÆ§ÕÉñ§ÕÌ§ÕÒ>§ÕÓù§ÕÔî§ÕÜ^§ÕÝ”§Õß	§Õà†§Õá‰§ÕáÀ§Õâ(§Õèà§Õé§ÕíÀ§Õî¶§Õó$§Õ÷l§Õû2§ÕûÑ§Õýì§Õÿç§Ö &§Ö >§Ö§Ö
Ö§ÖG§Ö(§Ö§Ö2§Öî§Ö ¶§Ö)ü§Ö+Þ§Ö/Â§Ö8Ñ§Ö;é§Ö@J§ÖBù§ÖE£§ÖH§ÖK"§ÖQ"§ÖS[§ÖVõ§ÖW‡§ÖXì§Ö\W§Ö^S§Ö`´§Öa8§Öi §Öi§Öpe§ÖtÁ§ÖtÎ§ÖwŽ§Öyõ§Ö}—§Ö£§Öƒc§Ö…‘§Ö’ú§Ö“«§Ö—S§Ö˜†§Ö˜ê§Ö™*§Ö =§Ö Å§Ö£H§Ö«"§Ö²»§Ö³?§Ö¶{§Ö¶÷§Ö·ö§Ö¸
§Ö¼x§Ö½Î§Ö¾,§ÖÀ·§ÖÃ)§ÖÄè§ÖÆ§ÖÊV§ÖËƒ§ÖÐP§ÖÙ`§Öß§Öâ»§Öâ½§Öã¢§ÖéT§Öê¦§Öì0§Öì[§Öí‚§ÖîÞ§ÖñL§Ö÷ì§ÖøÞ§× I§× §×!§×	¡§×
æ§× §×Ö§×§×+§×Ë§×˜§×‡§×[§×Ò§×â§×$§×&s§×)§×)g§×+Ë§×,_§×0¸§×5&§×6"§×8§×>8§×>‹§×@[§×@ë§×BR§×G§×H{§×L’§×MZ§×Nã§×X€§×XÎ§×Z§×^ú§×_§§×jã§×q…§×sg§×t"§×v.§×v{§×yG§×zg§×U§×Í§×˜§×„§×„*§×ˆ§×Š¢§×¿§×‘N§×œ§×œ=§×ž3§× §× †§× å§×¡§×£Û§×¥î§×§–§×¬w§×®Æ§×¯º§×²O§×´§×¶¯§×·O§×·\§×¸½§×½	§×ÄW§×Çd§×Ç†§×Èý§×ÊI§×Ê§×Ì`§×Ìº§×Î^§×ÎÄ§×à§×áý§×åf§×åÜ§×èP§×ëP§×ëc§×î:§×ö>§×øÊ§×þ£§Ø #§Ø~§ØŸ§Øé§ØÈ§Ø%§Ø%x§Ø%²§Ø+z§Ø-Ò§Ø.ç§Ø2O§Ø5
§Ø8v§Ø:–§Ø?§ØE=§ØL§ØM™§ØP¿§ØR²§ØU‚§ØWÜ§ØX§ØY§Ø[$§Ø\t§Ø\Ã§Ø]§Ø`'§Øb§Øb§ØfN§Ølc§Øn§ØnÑ§Øoÿ§Ørj§Øs²§ØxÃ§ØÄ§Ø—§Ø‡ §Ø‰h§Ø‰ß§Ø‹+§Ø”Ö§Ø˜§Øœ§Øk§Ø§Ø§§Ø¯4§Øµ‡§Øµ§§Ø¸t§Ø¹Ç§Ø¼/§Ø¾V§Ø¿ó§ØÅÉ§ØÅë§ØÆ-§ØÊb§ØÊ–§ØÎ§ØÒ½§ØÕû§ØÛˆ§ØÝC§ØÝÒ§ØÞ§§ØßD§Øá˜§ØãB§Øðb§Øø§Øùú§Øû§Ù ä§Ù	&§Ù	1§Ùs§Ù¸§Ù:§Ùº§Ùª§Ù!?§Ù*P§Ù/š§Ù0C§Ù;k§Ù;ž§Ù=_§Ù>¼§Ù@¶§Ù@Ó§ÙAK§ÙCÚ§ÙG‘§ÙLÆ§ÙNÎ§ÙPŸ§ÙR)§ÙVM§ÙW2§Ù[£§Ù_þ§Ùa™§Ùc­§Ùfp§Ùkx§Ùm°§Ù{Ê§Ù}W§Ù‚±§Ùƒ8§Ùˆ«§Ù˜ù§Ù™1§Ù›¾§ÙœD§Ùš§Ù¢ì§Ù£a§Ù¥§Ù²¥§Ù¹K§Ù»§Ù¼˜§Ù¾
§ÙÇ"§ÙÈ¥§ÙÌd§ÙÎr§ÙÎ½§Ù×y§ÙÜJ§Ùß¿§ÙßÇ§Ùìn§Ùñv§Ùñ›§Ùö
§Ùö%§ÙöË§Ù÷x§Ùýt§Ùý•§Ùþ]§Ú3§ÚÀ§Úþ§Ú§ÚÈ§Úï§Úl§Úâ§Ú"0§Ú$§Ú(È§Ú1S§Ú1h§Ú4×§Ú5S§Ú8•§Ú>¯§Ú?õ§ÚC|§ÚDS§ÚNî§ÚTÌ§ÚU§ÚUŠ§ÚXµ§Úa^§Úh6§Úli§Ún(§Ún}§Únü§Úoj§Úo§Úq½§Út²§Úz7§Úz„§Ú“§Ú…ù§Ús§ÚË§Ú›§Úã§Úž§Ú¡ï§Ú¡ÿ§Ú¢§Ú£§Ú­ §Ú´l§Ú¶e§Ú¸7§Ú¹z§ÚºR§Ú½•§Ú½§§Ú¿§ÚÆ]§ÚÊ˜§ÚÑ§ÚÑ§ÚÑa§ÚÒÜ§ÚÕ£§ÚÖõ§ÚÛf§ÚÞõ§Úà&§Úå§Úë§Úì§Úív§Úî¸§Úïú§Úó¹§Û J§Û m§Û w§Ûj§Û§Ûå§Ûƒ§ÛŸ§Ûg§Û<§Û ‘§Û#Ì§Û$§Û)ª§Û,§Û-š§Û.i§Û.ù§Û=§Û>Ù§ÛAª§ÛG§ÛI§ÛIœ§ÛJÎ§ÛJì§ÛU	§ÛUÝ§ÛV§Û_E§Û`ì§Ûfè§Ûh§Ûiû§ÛnÓ§Ûoj§Ûwn§ÛzI§Û{™§Û|³§Û}Ø§Ûƒ§Ûƒö§Û…2§Û†7§Ûˆ§Û‰~§Û‹ò§Û‹§Û½§Û˜“§Ûª6§ÛªÊ§Ûªî§Û­h§Û±ê§Û´4§Û¹Ÿ§Û»Ü§Û½§Û½”§ÛÅ†§ÛÇz§ÛÉI§ÛÏ §ÛÐA§ÛÑK§ÛÓ§ÛÔ§ÛÖ]§ÛÜà§ÛÞX§Ûãì§Ûæ~§Ûên§Ûêì§Ûìs§Ûì“§Ûï}§Ûó8§Ûôr§Üh§Üö§Ü§Ü2§Ü	§Ü‚§Ü§Üá§Ü³§Üµ§ÜÕ§Üµ§Üå§Ü1§Ü§Ü>§ÜG§Ü$v§Ü%§Ü(†§Ü*“§Ü,g§Ü,ÿ§Ü-î§Ü0ö§Ü1¼§Ü2&§Ü4=§Ü5ì§Ü:§ÜE-§ÜH§ÜH’§ÜJW§ÜK,§ÜKÉ§ÜN–§ÜS§ÜV˜§ÜZ!§Ü]§Ü_–§Üe§Ü½§Üƒ§Üƒ‰§Ü…D§Ü‰Ü§Ü‹s§Ü”¨§Ü›ã§Üž{§Ü¡©§Ü£Æ§Ü²Â§Ü·\§ÜÐî§ÜÑf§ÜÑ…§ÜÓ0§ÜÕi§Üäê§ÜíÉ§Üî¼§Üï*§Üïõ§Ü÷ÿ§Üý§Üý‘§Üÿg§ÝN§Ý §Ý^§ÝÔ§Ý©§Ý§Ý”§Ý²§Ý(§Ý"§Ýó§Ý(§ÝØ§Ý §Ý -§Ý!^§Ý!¡§Ý(t§Ý-n§Ý.‚§Ý/×§Ý0’§Ý5–§Ý5×§Ý:z§Ý>§Ý@ §ÝHÝ§ÝJU§ÝMÑ§ÝQ¥§ÝT„§ÝX§ÝZ“§Ý]T§Ý_.§Ýbß§ÝeU§Ýh;§Ýkx§Ým§ÝqÐ§Ýv•§Ýw§Ýˆ¬§Ý‰§ÝÄ§Ý‹§Ý“Ô§Ý %§Ý¡©§Ý£8§Ý«Ÿ§Ý¬§Ý³J§Ý´§Ý´›§Ý¸_§Ý¼y§Ý¼¸§Ý¿2§ÝÉƒ§ÝÎp§ÝÏÑ§ÝÕ|§ÝÖ¸§ÝÙ§ÝàÀ§ÝãË§Ýç¡§Ýíé§Ýð§Ýðd§Ýò¶§Ýô­§Ýôù§Ýøf§Ýù7§Ýùã§Ýû¶§Ýþî§Þ |§Þ:§Þa§Þ §Þ§Þ©§Þè§Þ³§ÞÙ§Þ 9§Þ#©§Þ'C§Þ)Ô§Þ+'§Þ+»§Þ2Õ§ÞC-§ÞF9§ÞJß§ÞLÓ§ÞUh§ÞV­§ÞW–§Þ]l§Þ^Ë§Þ_§Þbg§ÞgM§Þkª§Þp¹§ÞrÎ§Þt‘§Þ|e§ÞŒ§Þ€§Þ‡§Þ‡¡§ÞŠÀ§ÞŽœ§ÞŽÓ§Þ“G§Þ“o§Þ”Ü§Þ•€§Þš>§Þ¶§Þ©=§Þªª§Þªþ§Þ­A§Þ±ÿ§Þ³8§Þ¶R§Þ¾§ÞÒø§ÞØ§ÞÛê§ÞÜÏ§ÞÝé§ÞÞV§Þç©§Þë1§Þë˜§Þì÷§Þí¤§Þñ-§Þöò§Þû¾§ß§ß§ß"§ßî§ß…§ß7§ßë§ß'§ß§ß…§ß§ß{§ß«§ßž§ßã§ß´§ß)à§ß*¸§ß.8§ß/#§ß24§ß5˜§ß5™§ß6K§ß7^§ß9§ß95§ßCÌ§ßFm§ßLr§ßTÞ§ßY§ßdK§ßgß§ßhŒ§ßk£§ßl_§ßp²§ßq‘§ßx6§ß}{§ß€§ß†À§ß‹§ßŒ¸§ß”Ó§ß©¦§ß¯§ß³¯§ß¸T§ß»”§ß¿Û§ßÁê§ßÆÇ§ßÆá§ßËó§ßÍV§ßÔ"§ß×¯§ßÝ	§ßÝÈ§ßá=§ßñ§ßú¸§ßû¹§ßûÐ§ßüÏ§ßþ§à§àU§àÈ§à	§à§àš§à(§à 9§à#+§à$§à(R§à+x§à8Ê§à9N§à;ñ§à?Þ§àDÔ§àG‰§àMï§àR]§àTH§àU³§àUµ§àX§àY¸§à]ß§à^§à_D§à_f§àc§àf¼§àg#§àh²§àhÀ§àp@§àz<§àzA§à}©§à^§àƒ§àƒÖ§à„b§àˆØ§àŠK§àŒw§à‘V§à’”§à–§àšÈ§àžŒ§à«@§à­§à±§à±G§à²°§à³Õ§à´p§àµÆ§à·E§à¸¹§à¹J§àºù§à¼Û§àÃô§àÄì§àÍo§àØ™§àÚË§àÛp§àÛÃ§àÞ>§àâ§àäæ§àæ§àë°§àð§àó¢§àõb§àøZ§àú@§àýP§á,§á"§á?§á¾§á¹§á-§áÏ§á$§á$§§á+§á/;§á5§á52§á7N§á7ª§á8R§á9A§áE§áKÛ§áMW§áPÕ§áS‰§áUŠ§áVO§áW)§áWÏ§ácX§áfA§áh§áj §ám`§áp‚§ápí§át;§áyæ§á|§á‡(§áˆ§áÛ§áÜ§á•Ò§á—I§ážÖ§á¤§á§i§á§¾§á¨(§á¨¨§á©+§á±È§á²§áµ¹§á½Æ§áÀ9§áÃ¢§áÅ@§áÆó§áÇ