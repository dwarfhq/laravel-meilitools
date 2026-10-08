<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering\Concerns;

use BackedEnum;
use Closure;
use DateTimeInterface;
use Dwarf\MeiliTools\Filtering\FilterBuilder;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use Stringable;

/**
 * Builds MeiliSearch filter expressions with an Eloquent style API.
 *
 * @see https://www.meilisearch.com/docs/learn/filtering_and_sorting/filter_expression_reference
 */
trait BuildsFilters
{
    /**
     * Filter expressions with the boolean joining them to the previous expression.
     *
     * @var list<array{boolean: string, filter: string}>
     */
    protected array $filters = [];

    /**
     * Add a comparison, or a nested group of filters when given a closure.
     *
     * Comparing to null with `=` or `!=` is turned into an `IS NULL` or `IS NOT NULL` filter.
     * The parameters are untyped to stay compatible with Scout's `where()`.
     *
     * @param (Closure(FilterBuilder): mixed)|string $field
     * @param mixed                                  $operator
     * @param mixed                                  $value
     *
     * @return $this
     */
    public function where($field, $operator = null, $value = null, string $boolean = 'and'): static
    {
        if ($field instanceof Closure) {
            return $this->whereNested($field, $boolean);
        }

        [$operator, $value] = \func_num_args() === 2 ? ['=', $operator] : [$operator, $value];
        $operator = $operator === '<>' ? '!=' : $operator;

        if (!\in_array($operator, ['=', '!=', '>', '>=', '<', '<='], true)) {
            $operator = \is_scalar($operator) ? (string) $operator : get_debug_type($operator);

            throw new InvalidArgumentException(\sprintf('Invalid filter operator [%s]', $operator));
        }

        if ($value === null && !\in_array($operator, ['=', '!='], true)) {
            throw new InvalidArgumentException(\sprintf('Invalid filter operator [%s] for null', $operator));
        }

        if ($value === null) {
            return $operator === '=' ? $this->whereNull($field, $boolean) : $this->whereNotNull($field, $boolean);
        }

        return $this->addFilter(
            \sprintf('%s %s %s', $this->formatField($field), $operator, $this->formatValue($value)),
            $boolean,
        );
    }

    /**
     * Add an "or" comparison, or a nested group of filters when given a closure.
     *
     * @param (Closure(FilterBuilder): mixed)|string $field
     *
     * @return $this
     */
    public function orWhere(Closure|string $field, mixed $operator = null, mixed $value = null): static
    {
        [$operator, $value] = \func_num_args() === 2 ? ['=', $operator] : [$operator, $value];

        return $this->where($field, $operator, $value, 'or');
    }

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
    ): static {
        [$operator, $value] = \func_num_args() === 2 ? ['=', $operator] : [$operator, $value];
        $callback = $field instanceof Closure
            ? $field
            : fn (FilterBuilder $filter): FilterBuilder => $filter->where($field, $operator, $value);

        return $this->whereNested($callback, $boolean, true);
    }

    /**
     * Add an "or" negated comparison, or a negated nested group of filters when given a closure.
     *
     * @param (Closure(FilterBuilder): mixed)|string $field
     *
     * @return $this
     */
    public function orWhereNot(Closure|string $field, mixed $operator = null, mixed $value = null): static
    {
        [$operator, $value] = \func_num_args() === 2 ? ['=', $operator] : [$operator, $value];

        return $this->whereNot($field, $operator, $value, 'or');
    }

    /**
     * Add a nested group of filters.
     *
     * @param Closure(FilterBuilder): mixed $callback
     *
     * @return $this
     */
    public function whereNested(Closure $callback, string $boolean = 'and', bool $not = false): static
    {
        $callback($filter = new FilterBuilder());
        if ($filter->toFilter() === '') {
            return $this;
        }

        return $this->addFilter(\sprintf('(%s)', $filter->toFilter()), $boolean, $not);
    }

    /**
     * Add a raw filter expression, which is wrapped in parentheses.
     *
     * @return $this
     */
    public function whereRaw(string $filter, string $boolean = 'and'): static
    {
        return $this->addFilter(\sprintf('(%s)', $filter), $boolean);
    }

    /**
     * Add an "or" raw filter expression, which is wrapped in parentheses.
     *
     * @return $this
     */
    public function orWhereRaw(string $filter): static
    {
        return $this->whereRaw($filter, 'or');
    }

    /**
     * Add an `IN` filter.
     *
     * The parameters are untyped to stay compatible with Scout's `whereIn()`.
     *
     * @param string                                      $field
     * @param Arrayable<array-key, mixed>|iterable<mixed> $values
     *
     * @return $this
     */
    public function whereIn($field, $values, string $boolean = 'and', bool $not = false): static
    {
        $values = $values instanceof Arrayable ? $values->toArray() : $values;
        $values = array_map($this->formatValue(...), [...$values]);

        return $this->addFilter(
            \sprintf('%s %s [%s]', $this->formatField($field), $not ? 'NOT IN' : 'IN', implode(', ', $values)),
            $boolean,
        );
    }

    /**
     * Add an "or" `IN` filter.
     *
     * @param Arrayable<array-key, mixed>|iterable<mixed> $values
     *
     * @return $this
     */
    public function orWhereIn(string $field, Arrayable|iterable $values): static
    {
        return $this->whereIn($field, $values, 'or');
    }

    /**
     * Add a `NOT IN` filter.
     *
     * The parameters are untyped to stay compatible with Scout's `whereNotIn()`.
     *
     * @param string                                      $field
     * @param Arrayable<array-key, mixed>|iterable<mixed> $values
     *
     * @return $this
     */
    public function whereNotIn($field, $values, string $boolean = 'and'): static
    {
        return $this->whereIn($field, $values, $boolean, true);
    }

    /**
     * Add an "or" `NOT IN` filter.
     *
     * @param Arrayable<array-key, mixed>|iterable<mixed> $values
     *
     * @return $this
     */
    public function orWhereNotIn(string $field, Arrayable|iterable $values): static
    {
        return $this->whereNotIn($field, $values, 'or');
    }

    /**
     * Add a `TO` range filter, where both values are inclusive.
     *
     * @param iterable<mixed> $values The low and high values.
     *
     * @return $this
     */
    public function whereBetween(string $field, iterable $values, string $boolean = 'and', bool $not = false): static
    {
        $values = array_values([...$values]);
        if (\count($values) !== 2) {
            throw new InvalidArgumentException('A between filter requires exactly two values');
        }

        return $this->addFilter(
            \sprintf('%s %s TO %s', $field, $this->formatValue($values[0]), $this->formatValue($values[1])),
            $boolean,
            $not,
        );
    }

    /**
     * Add an "or" `TO` range filter.
     *
     * @param iterable<mixed> $values The low and high values.
     *
     * @return $this
     */
    public function orWhereBetween(string $field, iterable $values): static
    {
        return $this->whereBetween($field, $values, 'or');
    }

    /**
     * Add a negated `TO` range filter.
     *
     * @param iterable<mixed> $values The low and high values.
     *
     * @return $this
     */
    public function whereNotBetween(string $field, iterable $values, string $boolean = 'and'): static
    {
        return $this->whereBetween($field, $values, $boolean, true);
    }

    /**
     * Add an "or" negated `TO` range filter.
     *
     * @param iterable<mixed> $values The low and high values.
     *
     * @return $this
     */
    public function orWhereNotBetween(string $field, iterable $values): static
    {
        return $this->whereNotBetween($field, $values, 'or');
    }

    /**
     * Add an `IS NULL` filter.
     *
     * @return $this
     */
    public function whereNull(string $field, string $boolean = 'and', bool $not = false): static
    {
        return $this->addFilter(\sprintf('%s %s', $field, $not ? 'IS NOT NULL' : 'IS NULL'), $boolean);
    }

    /**
     * Add an "or" `IS NULL` filter.
     *
     * @return $this
     */
    public function orWhereNull(string $field): static
    {
        return $this->whereNull($field, 'or');
    }

    /**
     * Add an `IS NOT NULL` filter, which also matches documents without the field.
     *
     * @return $this
     */
    public function whereNotNull(string $field, string $boolean = 'and'): static
    {
        return $this->whereNull($field, $boolean, true);
    }

    /**
     * Add an "or" `IS NOT NULL` filter, which also matches documents without the field.
     *
     * @return $this
     */
    public function orWhereNotNull(string $field): static
    {
        return $this->whereNotNull($field, 'or');
    }

    /**
     * Add an `IS EMPTY` filter, matching empty strings, arrays and objects.
     *
     * @return $this
     */
    public function whereEmpty(string $field, string $boolean = 'and', bool $not = false): static
    {
        return $this->addFilter(\sprintf('%s %s', $field, $not ? 'IS NOT EMPTY' : 'IS EMPTY'), $boolean);
    }

    /**
     * Add an "or" `IS EMPTY` filter.
     *
     * @return $this
     */
    public function orWhereEmpty(string $field): static
    {
        return $this->whereEmpty($field, 'or');
    }

    /**
     * Add an `IS NOT EMPTY` filter, which also matches documents without the field.
     *
     * @return $this
     */
    public function whereNotEmpty(string $field, string $boolean = 'and'): static
    {
        return $this->whereEmpty($field, $boolean, true);
    }

    /**
     * Add an "or" `IS NOT EMPTY` filter.
     *
     * @return $this
     */
    public function orWhereNotEmpty(string $field): static
    {
        return $this->whereNotEmpty($field, 'or');
    }

    /**
     * Add an `EXISTS` filter, matching documents containing the field, even when null or empty.
     *
     * @return $this
     */
    public function whereExists(string $field, string $boolean = 'and', bool $not = false): static
    {
        return $this->addFilter(\sprintf('%s %s', $field, $not ? 'NOT EXISTS' : 'EXISTS'), $boolean);
    }

    /**
     * Add an "or" `EXISTS` filter.
     *
     * @return $this
     */
    public function orWhereExists(string $field): static
    {
        return $this->whereExists($field, 'or');
    }

    /**
     * Add a `NOT EXISTS` filter.
     *
     * @return $this
     */
    public function whereNotExists(string $field, string $boolean = 'and'): static
    {
        return $this->whereExists($field, $boolean, true);
    }

    /**
     * Add an "or" `NOT EXISTS` filter.
     *
     * @return $this
     */
    public function orWhereNotExists(string $field): static
    {
        return $this->whereNotExists($field, 'or');
    }

    /**
     * Add a `STARTS WITH` filter.
     *
     * @return $this
     */
    public function whereStartsWith(string $field, string $value, string $boolean = 'and', bool $not = false): static
    {
        return $this->addFilter(
            \sprintf('%s %s %s', $field, $not ? 'NOT STARTS WITH' : 'STARTS WITH', $this->formatValue($value)),
            $boolean,
        );
    }

    /**
     * Add an "or" `STARTS WITH` filter.
     *
     * @return $this
     */
    public function orWhereStartsWith(string $field, string $value): static
    {
        return $this->whereStartsWith($field, $value, 'or');
    }

    /**
     * Add a `NOT STARTS WITH` filter.
     *
     * @return $this
     */
    public function whereNotStartsWith(string $field, string $value, string $boolean = 'and'): static
    {
        return $this->whereStartsWith($field, $value, $boolean, true);
    }

    /**
     * Add an "or" `NOT STARTS WITH` filter.
     *
     * @return $this
     */
    public function orWhereNotStartsWith(string $field, string $value): static
    {
        return $this->whereNotStartsWith($field, $value, 'or');
    }

    /**
     * Add a `CONTAINS` filter, which requires the experimental `containsFilter` feature to be enabled.
     *
     * @return $this
     */
    public function whereContains(string $field, string $value, string $boolean = 'and', bool $not = false): static
    {
        return $this->addFilter(
            \sprintf('%s %s %s', $field, $not ? 'NOT CONTAINS' : 'CONTAINS', $this->formatValue($value)),
            $boolean,
        );
    }

    /**
     * Add an "or" `CONTAINS` filter.
     *
     * @return $this
     */
    public function orWhereContains(string $field, string $value): static
    {
        return $this->whereContains($field, $value, 'or');
    }

    /**
     * Add a `NOT CONTAINS` filter.
     *
     * @return $this
     */
    public function whereNotContains(string $field, string $value, string $boolean = 'and'): static
    {
        return $this->whereContains($field, $value, $boolean, true);
    }

    /**
     * Add an "or" `NOT CONTAINS` filter.
     *
     * @return $this
     */
    public function orWhereNotContains(string $field, string $value): static
    {
        return $this->whereNotContains($field, $value, 'or');
    }

    /**
     * Add a `_geoRadius` filter, with the distance in meters.
     *
     * @return $this
     */
    public function whereGeoRadius(
        float $lat,
        float $lng,
        int $distance,
        ?int $resolution = null,
        string $boolean = 'and',
        bool $not = false,
    ): static {
        $arguments = [$lat, $lng, $distance];
        if ($resolution !== null) {
            $arguments[] = $resolution;
        }

        return $this->addFilter(
            \sprintf('_geoRadius(%s)', implode(', ', array_map($this->formatNumber(...), $arguments))),
            $boolean,
            $not,
        );
    }

    /**
     * Add an "or" `_geoRadius` filter.
     *
     * @return $this
     */
    public function orWhereGeoRadius(float $lat, float $lng, int $distance, ?int $resolution = null): static
    {
        return $this->whereGeoRadius($lat, $lng, $distance, $resolution, 'or');
    }

    /**
     * Add a negated `_geoRadius` filter.
     *
     * @return $this
     */
    public function whereNotGeoRadius(
        float $lat,
        float $lng,
        int $distance,
        ?int $resolution = null,
        string $boolean = 'and',
    ): static {
        return $this->whereGeoRadius($lat, $lng, $distance, $resolution, $boolean, true);
    }

    /**
     * Add an "or" negated `_geoRadius` filter.
     *
     * @return $this
     */
    public function orWhereNotGeoRadius(float $lat, float $lng, int $distance, ?int $resolution = null): static
    {
        return $this->whereNotGeoRadius($lat, $lng, $distance, $resolution, 'or');
    }

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
    ): static {
        return $this->addFilter(
            \sprintf('_geoBoundingBox(%s, %s)', $this->formatPoint($topRight), $this->formatPoint($bottomLeft)),
            $boolean,
            $not,
        );
    }

    /**
     * Add an "or" `_geoBoundingBox` filter.
     *
     * @param array{float|int, float|int} $topRight
     * @param array{float|int, float|int} $bottomLeft
     *
     * @return $this
     */
    public function orWhereGeoBoundingBox(array $topRight, array $bottomLeft): static
    {
        return $this->whereGeoBoundingBox($topRight, $bottomLeft, 'or');
    }

    /**
     * Add a negated `_geoBoundingBox` filter.
     *
     * @param array{float|int, float|int} $topRight
     * @param array{float|int, float|int} $bottomLeft
     *
     * @return $this
     */
    public function whereNotGeoBoundingBox(array $topRight, array $bottomLeft, string $boolean = 'and'): static
    {
        return $this->whereGeoBoundingBox($topRight, $bottomLeft, $boolean, true);
    }

    /**
     * Add an "or" negated `_geoBoundingBox` filter.
     *
     * @param array{float|int, float|int} $topRight
     * @param array{float|int, float|int} $bottomLeft
     *
     * @return $this
     */
    public function orWhereNotGeoBoundingBox(array $topRight, array $bottomLeft): static
    {
        return $this->whereNotGeoBoundingBox($topRight, $bottomLeft, 'or');
    }

    /**
     * Add a `_geoPolygon` filter, which requires `_geojson` to be filterable.
     *
     * @param list<array{float|int, float|int}> $points Latitude and longitude of at least three points.
     *
     * @return $this
     */
    public function whereGeoPolygon(array $points, string $boolean = 'and', bool $not = false): static
    {
        if (\count($points) < 3) {
            throw new InvalidArgumentException('A geo polygon filter requires at least three points');
        }

        return $this->addFilter(
            \sprintf('_geoPolygon(%s)', implode(', ', array_map($this->formatPoint(...), $points))),
            $boolean,
            $not,
        );
    }

    /**
     * Add an "or" `_geoPolygon` filter.
     *
     * @param list<array{float|int, float|int}> $points
     *
     * @return $this
     */
    public function orWhereGeoPolygon(array $points): static
    {
        return $this->whereGeoPolygon($points, 'or');
    }

    /**
     * Add a negated `_geoPolygon` filter.
     *
     * @param list<array{float|int, float|int}> $points
     *
     * @return $this
     */
    public function whereNotGeoPolygon(array $points, string $boolean = 'and'): static
    {
        return $this->whereGeoPolygon($points, $boolean, true);
    }

    /**
     * Add an "or" negated `_geoPolygon` filter.
     *
     * @param list<array{float|int, float|int}> $points
     *
     * @return $this
     */
    public function orWhereNotGeoPolygon(array $points): static
    {
        return $this->whereNotGeoPolygon($points, 'or');
    }

    /**
     * Get the MeiliSearch filter expression.
     */
    public function toFilter(): string
    {
        $expression = '';
        foreach ($this->filters as $index => $filter) {
            $expression .= ($index === 0 ? '' : ' ' . $filter['boolean'] . ' ') . $filter['filter'];
        }

        return $expression;
    }

    /**
     * Add a filter expression.
     *
     * @return $this
     */
    protected function addFilter(string $filter, string $boolean, bool $not = false): static
    {
        $boolean = strtoupper($boolean);
        if (!\in_array($boolean, ['AND', 'OR'], true)) {
            throw new InvalidArgumentException(\sprintf('Invalid filter boolean [%s]', $boolean));
        }

        $this->filters[] = ['boolean' => $boolean, 'filter' => ($not ? 'NOT ' : '') . $filter];

        return $this;
    }

    /**
     * Format a value for a filter expression.
     *
     * Dates are converted to Unix timestamps, so they must be indexed as timestamps to be filterable.
     */
    protected function formatValue(mixed $value): string
    {
        return match (true) {
            \is_bool($value)                    => $value ? 'true' : 'false',
            \is_int($value), \is_float($value)  => $this->formatNumber($value),
            \is_string($value)                  => '"' . addcslashes($value, '"\\') . '"',
            $value instanceof DateTimeInterface => (string) $value->getTimestamp(),
            $value instanceof BackedEnum        => $this->formatValue($value->value),
            $value instanceof Stringable        => $this->formatValue((string) $value),
            default                             => throw new InvalidArgumentException(
                \sprintf('Unsupported filter value of type [%s]', get_debug_type($value)),
            ),
        };
    }

    /**
     * Format a number for a filter expression, without exponent notation.
     */
    protected function formatNumber(float|int $number): string
    {
        if (\is_int($number)) {
            return (string) $number;
        }

        if (!is_finite($number)) {
            throw new InvalidArgumentException('Filter numbers must be finite');
        }

        $formatted = (string) $number;

        return str_contains($formatted, 'E')
            ? rtrim(rtrim(\sprintf('%.20F', $number), '0'), '.')
            : $formatted;
    }

    /**
     * Ensure a field name is a non-empty string.
     */
    protected function formatField(mixed $field): string
    {
        if (!\is_string($field) || $field === '') {
            throw new InvalidArgumentException('Filter fields must be non-empty strings');
        }

        return $field;
    }

    /**
     * Format a latitude and longitude pair for a geo filter.
     *
     * @param array<array-key, mixed> $point
     */
    protected function formatPoint(array $point): string
    {
        $point = array_values($point);
        if (\count($point) !== 2 || !is_numeric($point[0]) || !is_numeric($point[1])) {
            throw new InvalidArgumentException('Geo points must contain a latitude and a longitude');
        }

        return \sprintf('[%s, %s]', $this->formatNumber(+$point[0]), $this->formatNumber(+$point[1]));
    }
}
