<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Filtering;

use Closure;
use Dwarf\MeiliTools\Filtering\DistanceUnit;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;

/**
 * Builds MeiliSearch filter expressions with an Eloquent style API.
 *
 * @see https://www.meilisearch.com/docs/learn/filtering_and_sorting/filter_expression_reference
 */
interface FilterBuilder
{
    /**
     * Add a comparison, or a nested group of filters when given a closure.
     *
     * Comparing to null with `=` or `!=` is turned into an `IS NULL` or `IS NOT NULL` filter.
     * The parameters are mixed to stay compatible with Scout's `where()`.
     *
     * @param (Closure(FilterBuilder): mixed)|string $field
     *
     * @return $this
     */
    public function where(mixed $field, mixed $operator = null, mixed $value = null, string $boolean = 'and'): static;

    /**
     * Add an "or" comparison, or a nested group of filters when given a closure.
     *
     * @param (Closure(FilterBuilder): mixed)|string $field
     *
     * @return $this
     */
    public function orWhere(Closure|string $field, mixed $operator = null, mixed $value = null): static;

    /**
     * Add a negated comparison, or a negated nested group of filters when given a closure.
     *
     * @param (Closure(FilterBuilder): mixed)|string $field
     *
     * @return $this
     */
    public function whereNot(
        Closure|string $field,
        mixed $operator = null,
        mixed $value = null,
        string $boolean = 'and',
    ): static;

    /**
     * Add an "or" negated comparison, or a negated nested group of filters when given a closure.
     *
     * @param (Closure(FilterBuilder): mixed)|string $field
     *
     * @return $this
     */
    public function orWhereNot(Closure|string $field, mixed $operator = null, mixed $value = null): static;

    /**
     * Add a nested group of filters.
     *
     * @param Closure(FilterBuilder): mixed $callback
     *
     * @return $this
     */
    public function whereNested(Closure $callback, string $boolean = 'and', bool $not = false): static;

    /**
     * Add a raw filter expression, which is wrapped in parentheses.
     *
     * @return $this
     */
    public function whereRaw(string $filter, string $boolean = 'and'): static;

    /**
     * Add an "or" raw filter expression, which is wrapped in parentheses.
     *
     * @return $this
     */
    public function orWhereRaw(string $filter): static;

    /**
     * Add an `IN` filter.
     *
     * The parameters are mixed to stay compatible with Scout's `whereIn()`.
     *
     * @param string                                      $field
     * @param Arrayable<array-key, mixed>|iterable<mixed> $values
     *
     * @return $this
     */
    public function whereIn(mixed $field, mixed $values, string $boolean = 'and', bool $not = false): static;

    /**
     * Add an "or" `IN` filter.
     *
     * @param Arrayable<array-key, mixed>|iterable<mixed> $values
     *
     * @return $this
     */
    public function orWhereIn(string $field, Arrayable|iterable $values): static;

    /**
     * Add a `NOT IN` filter.
     *
     * The parameters are mixed to stay compatible with Scout's `whereNotIn()`.
     *
     * @param string                                      $field
     * @param Arrayable<array-key, mixed>|iterable<mixed> $values
     *
     * @return $this
     */
    public function whereNotIn(mixed $field, mixed $values, string $boolean = 'and'): static;

    /**
     * Add an "or" `NOT IN` filter.
     *
     * @param Arrayable<array-key, mixed>|iterable<mixed> $values
     *
     * @return $this
     */
    public function orWhereNotIn(string $field, Arrayable|iterable $values): static;

    /**
     * Add a `TO` range filter, where both values are inclusive.
     *
     * @param iterable<mixed> $values The low and high values.
     *
     * @return $this
     */
    public function whereBetween(string $field, iterable $values, string $boolean = 'and', bool $not = false): static;

    /**
     * Add an "or" `TO` range filter.
     *
     * @param iterable<mixed> $values The low and high values.
     *
     * @return $this
     */
    public function orWhereBetween(string $field, iterable $values): static;

    /**
     * Add a negated `TO` range filter.
     *
     * @param iterable<mixed> $values The low and high values.
     *
     * @return $this
     */
    public function whereNotBetween(string $field, iterable $values, string $boolean = 'and'): static;

    /**
     * Add an "or" negated `TO` range filter.
     *
     * @param iterable<mixed> $values The low and high values.
     *
     * @return $this
     */
    public function orWhereNotBetween(string $field, iterable $values): static;

    /**
     * Add an `IS NULL` filter.
     *
     * @return $this
     */
    public function whereNull(string $field, string $boolean = 'and', bool $not = false): static;

    /**
     * Add an "or" `IS NULL` filter.
     *
     * @return $this
     */
    public function orWhereNull(string $field): static;

    /**
     * Add an `IS NOT NULL` filter, which also matches documents without the field.
     *
     * @return $this
     */
    public function whereNotNull(string $field, string $boolean = 'and'): static;

    /**
     * Add an "or" `IS NOT NULL` filter, which also matches documents without the field.
     *
     * @return $this
     */
    public function orWhereNotNull(string $field): static;

    /**
     * Add an `IS EMPTY` filter, matching empty strings, arrays and objects.
     *
     * @return $this
     */
    public function whereEmpty(string $field, string $boolean = 'and', bool $not = false): static;

    /**
     * Add an "or" `IS EMPTY` filter.
     *
     * @return $this
     */
    public function orWhereEmpty(string $field): static;

    /**
     * Add an `IS NOT EMPTY` filter, which also matches documents without the field.
     *
     * @return $this
     */
    public function whereNotEmpty(string $field, string $boolean = 'and'): static;

    /**
     * Add an "or" `IS NOT EMPTY` filter.
     *
     * @return $this
     */
    public function orWhereNotEmpty(string $field): static;

    /**
     * Add an `EXISTS` filter, matching documents containing the field, even when null or empty.
     *
     * @return $this
     */
    public function whereExists(string $field, string $boolean = 'and', bool $not = false): static;

    /**
     * Add an "or" `EXISTS` filter.
     *
     * @return $this
     */
    public function orWhereExists(string $field): static;

    /**
     * Add a `NOT EXISTS` filter.
     *
     * @return $this
     */
    public function whereNotExists(string $field, string $boolean = 'and'): static;

    /**
     * Add an "or" `NOT EXISTS` filter.
     *
     * @return $this
     */
    public function orWhereNotExists(string $field): static;

    /**
     * Add a `STARTS WITH` filter.
     *
     * @return $this
     */
    public function whereStartsWith(string $field, string $value, string $boolean = 'and', bool $not = false): static;

    /**
     * Add an "or" `STARTS WITH` filter.
     *
     * @return $this
     */
    public function orWhereStartsWith(string $field, string $value): static;

    /**
     * Add a `NOT STARTS WITH` filter.
     *
     * @return $this
     */
    public function whereNotStartsWith(string $field, string $value, string $boolean = 'and'): static;

    /**
     * Add an "or" `NOT STARTS WITH` filter.
     *
     * @return $this
     */
    public function orWhereNotStartsWith(string $field, string $value): static;

    /**
     * Add a `CONTAINS` filter, which requires the experimental `containsFilter` feature to be enabled.
     *
     * @return $this
     */
    public function whereContains(string $field, string $value, string $boolean = 'and', bool $not = false): static;

    /**
     * Add an "or" `CONTAINS` filter.
     *
     * @return $this
     */
    public function orWhereContains(string $field, string $value): static;

    /**
     * Add a `NOT CONTAINS` filter.
     *
     * @return $this
     */
    public function whereNotContains(string $field, string $value, string $boolean = 'and'): static;

    /**
     * Add an "or" `NOT CONTAINS` filter.
     *
     * @return $this
     */
    public function orWhereNotContains(string $field, string $value): static;

    /**
     * Add a `_geoRadius` filter.
     *
     * The resolution only applies to `_geojson` shapes, which are matched against a polygon with that number
     * of points approximating the circle, between 3 and 1000 (MeiliSearch defaults to 125).
     *
     * @throws InvalidArgumentException When the distance is negative or the resolution is out of range.
     *
     * @return $this
     */
    public function whereGeoRadius(
        float $lat,
        float $lng,
        float|int $distance,
        DistanceUnit $unit = DistanceUnit::Meters,
        ?int $resolution = null,
        string $boolean = 'and',
        bool $not = false,
    ): static;

    /**
     * Add an "or" `_geoRadius` filter.
     *
     * @return $this
     */
    public function orWhereGeoRadius(
        float $lat,
        float $lng,
        float|int $distance,
        DistanceUnit $unit = DistanceUnit::Meters,
        ?int $resolution = null,
    ): static;

    /**
     * Add a negated `_geoRadius` filter.
     *
     * @return $this
     */
    public function whereNotGeoRadius(
        float $lat,
        float $lng,
        float|int $distance,
        DistanceUnit $unit = DistanceUnit::Meters,
        ?int $resolution = null,
        string $boolean = 'and',
    ): static;

    /**
     * Add an "or" negated `_geoRadius` filter.
     *
     * @return $this
     */
    public function orWhereNotGeoRadius(
        float $lat,
        float $lng,
        float|int $distance,
        DistanceUnit $unit = DistanceUnit::Meters,
        ?int $resolution = null,
    ): static;

    /**
     * Add a `_geoBoundingBox` filter.
     *
     * @param array{float|int, float|int} $topRight   Latitude and longitude.
     * @param array{float|int, float|int} $bottomLeft Latitude and longitude.
     *
     * @return $this
     */
    public function whereGeoBoundingBox(
        array $topRight,
        array $bottomLeft,
        string $boolean = 'and',
        bool $not = false,
    ): static;

    /**
     * Add an "or" `_geoBoundingBox` filter.
     *
     * @param array{float|int, float|int} $topRight
     * @param array{float|int, float|int} $bottomLeft
     *
     * @return $this
     */
    public function orWhereGeoBoundingBox(array $topRight, array $bottomLeft): static;

    /**
     * Add a negated `_geoBoundingBox` filter.
     *
     * @param array{float|int, float|int} $topRight
     * @param array{float|int, float|int} $bottomLeft
     *
     * @return $this
     */
    public function whereNotGeoBoundingBox(array $topRight, array $bottomLeft, string $boolean = 'and'): static;

    /**
     * Add an "or" negated `_geoBoundingBox` filter.
     *
     * @param array{float|int, float|int} $topRight
     * @param array{float|int, float|int} $bottomLeft
     *
     * @return $this
     */
    public function orWhereNotGeoBoundingBox(array $topRight, array $bottomLeft): static;

    /**
     * Add a `_geoPolygon` filter, which requires `_geojson` to be filterable.
     *
     * @param list<array{float|int, float|int}> $points Latitude and longitude of at least three points.
     *
     * @return $this
     */
    public function whereGeoPolygon(array $points, string $boolean = 'and', bool $not = false): static;

    /**
     * Add an "or" `_geoPolygon` filter.
     *
     * @param list<array{float|int, float|int}> $points
     *
     * @return $this
     */
    public function orWhereGeoPolygon(array $points): static;

    /**
     * Add a negated `_geoPolygon` filter.
     *
     * @param list<array{float|int, float|int}> $points
     *
     * @return $this
     */
    public function whereNotGeoPolygon(array $points, string $boolean = 'and'): static;

    /**
     * Add an "or" negated `_geoPolygon` filter.
     *
     * @param list<array{float|int, float|int}> $points
     *
     * @return $this
     */
    public function orWhereNotGeoPolygon(array $points): static;

    /**
     * Get the MeiliSearch filter expression.
     */
    public function toFilter(): string;
}
