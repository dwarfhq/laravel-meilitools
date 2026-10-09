<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering\Concerns;

use Closure;
use Dwarf\MeiliTools\Contracts\Filtering\FilterBuilder;
use Dwarf\MeiliTools\Contracts\Filtering\FormatsFilterValues;
use Dwarf\MeiliTools\Enums\Filtering\DistanceUnit;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;

/**
 * Implements the filter builder contract.
 *
 * @see FilterBuilder
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
     * Formatter for fields and values.
     */
    protected ?FormatsFilterValues $formatter = null;

    /**
     * {@inheritDoc}
     *
     * @param (Closure(FilterBuilder): mixed)|string $field
     *
     * @return $this
     */
    public function where(mixed $field, mixed $operator = null, mixed $value = null, string $boolean = 'and'): static
    {
        if ($field instanceof Closure) {
            return $this->whereNested($field, $boolean);
        }

        [$operator, $value] = $this->prepareValueAndOperator($operator, $value, \func_num_args() === 2);

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

        $format = $this->formatter();
        $filter = \sprintf('%s %s %s', $format->field($field), $operator, $format->value($value));

        return $this->addFilter($filter, $boolean);
    }

    public function orWhere(Closure|string $field, mixed $operator = null, mixed $value = null): static
    {
        [$operator, $value] = $this->prepareValueAndOperator($operator, $value, \func_num_args() === 2);

        return $this->where($field, $operator, $value, 'or');
    }

    public function whereNot(
        Closure|string $field,
        mixed $operator = null,
        mixed $value = null,
        string $boolean = 'and',
    ): static {
        [$operator, $value] = $this->prepareValueAndOperator($operator, $value, \func_num_args() === 2);
        $callback = $field instanceof Closure
            ? $field
            : fn (FilterBuilder $filter): FilterBuilder => $filter->where($field, $operator, $value);

        return $this->whereNested($callback, $boolean, true);
    }

    public function orWhereNot(Closure|string $field, mixed $operator = null, mixed $value = null): static
    {
        [$operator, $value] = $this->prepareValueAndOperator($operator, $value, \func_num_args() === 2);

        return $this->whereNot($field, $operator, $value, 'or');
    }

    public function whereNested(Closure $callback, string $boolean = 'and', bool $not = false): static
    {
        $callback($filter = resolve(FilterBuilder::class));
        if ($filter->toFilter() === '') {
            return $this;
        }

        return $this->addFilter(\sprintf('(%s)', $filter->toFilter()), $boolean, $not);
    }

    public function whereRaw(string $filter, string $boolean = 'and'): static
    {
        return $this->addFilter(\sprintf('(%s)', $filter), $boolean);
    }

    public function orWhereRaw(string $filter): static
    {
        return $this->whereRaw($filter, 'or');
    }

    /**
     * {@inheritDoc}
     *
     * @param string                                      $field
     * @param Arrayable<array-key, mixed>|iterable<mixed> $values
     *
     * @return $this
     */
    public function whereIn(mixed $field, mixed $values, string $boolean = 'and', bool $not = false): static
    {
        $format = $this->formatter();
        $values = $values instanceof Arrayable ? $values->toArray() : $values;
        // Null never matches a list, like in SQL, so it's left out instead of failing.
        $values = array_filter([...$values], fn (mixed $value): bool => $value !== null);
        $values = implode(', ', array_map($format->value(...), $values));

        $filter = \sprintf('%s %s [%s]', $format->field($field), $not ? 'NOT IN' : 'IN', $values);

        return $this->addFilter($filter, $boolean);
    }

    public function orWhereIn(string $field, Arrayable|iterable $values): static
    {
        return $this->whereIn($field, $values, 'or');
    }

    /**
     * {@inheritDoc}
     *
     * @param string                                      $field
     * @param Arrayable<array-key, mixed>|iterable<mixed> $values
     *
     * @return $this
     */
    public function whereNotIn(mixed $field, mixed $values, string $boolean = 'and'): static
    {
        return $this->whereIn($field, $values, $boolean, true);
    }

    public function orWhereNotIn(string $field, Arrayable|iterable $values): static
    {
        return $this->whereNotIn($field, $values, 'or');
    }

    public function whereBetween(string $field, iterable $values, string $boolean = 'and', bool $not = false): static
    {
        $values = array_values([...$values]);
        if (\count($values) !== 2) {
            throw new InvalidArgumentException('A between filter requires exactly two values');
        }

        $format = $this->formatter();
        [$low, $high] = [$format->value($values[0]), $format->value($values[1])];
        $filter = \sprintf('%s %s TO %s', $format->field($field), $low, $high);

        return $this->addFilter($filter, $boolean, $not);
    }

    public function orWhereBetween(string $field, iterable $values): static
    {
        return $this->whereBetween($field, $values, 'or');
    }

    public function whereNotBetween(string $field, iterable $values, string $boolean = 'and'): static
    {
        return $this->whereBetween($field, $values, $boolean, true);
    }

    public function orWhereNotBetween(string $field, iterable $values): static
    {
        return $this->whereNotBetween($field, $values, 'or');
    }

    public function whereNull(string $field, string $boolean = 'and', bool $not = false): static
    {
        return $this->addFieldFilter($field, $not ? 'IS NOT NULL' : 'IS NULL', $boolean);
    }

    public function orWhereNull(string $field): static
    {
        return $this->whereNull($field, 'or');
    }

    public function whereNotNull(string $field, string $boolean = 'and'): static
    {
        return $this->whereNull($field, $boolean, true);
    }

    public function orWhereNotNull(string $field): static
    {
        return $this->whereNotNull($field, 'or');
    }

    public function whereEmpty(string $field, string $boolean = 'and', bool $not = false): static
    {
        return $this->addFieldFilter($field, $not ? 'IS NOT EMPTY' : 'IS EMPTY', $boolean);
    }

    public function orWhereEmpty(string $field): static
    {
        return $this->whereEmpty($field, 'or');
    }

    public function whereNotEmpty(string $field, string $boolean = 'and'): static
    {
        return $this->whereEmpty($field, $boolean, true);
    }

    public function orWhereNotEmpty(string $field): static
    {
        return $this->whereNotEmpty($field, 'or');
    }

    public function whereExists(string $field, string $boolean = 'and', bool $not = false): static
    {
        return $this->addFieldFilter($field, $not ? 'NOT EXISTS' : 'EXISTS', $boolean);
    }

    public function orWhereExists(string $field): static
    {
        return $this->whereExists($field, 'or');
    }

    public function whereNotExists(string $field, string $boolean = 'and'): static
    {
        return $this->whereExists($field, $boolean, true);
    }

    public function orWhereNotExists(string $field): static
    {
        return $this->whereNotExists($field, 'or');
    }

    public function whereStartsWith(string $field, string $value, string $boolean = 'and', bool $not = false): static
    {
        $value = $this->formatter()->value($value);

        return $this->addFieldFilter($field, ($not ? 'NOT STARTS WITH ' : 'STARTS WITH ') . $value, $boolean);
    }

    public function orWhereStartsWith(string $field, string $value): static
    {
        return $this->whereStartsWith($field, $value, 'or');
    }

    public function whereNotStartsWith(string $field, string $value, string $boolean = 'and'): static
    {
        return $this->whereStartsWith($field, $value, $boolean, true);
    }

    public function orWhereNotStartsWith(string $field, string $value): static
    {
        return $this->whereNotStartsWith($field, $value, 'or');
    }

    public function whereContains(string $field, string $value, string $boolean = 'and', bool $not = false): static
    {
        $value = $this->formatter()->value($value);

        return $this->addFieldFilter($field, ($not ? 'NOT CONTAINS ' : 'CONTAINS ') . $value, $boolean);
    }

    public function orWhereContains(string $field, string $value): static
    {
        return $this->whereContains($field, $value, 'or');
    }

    public function whereNotContains(string $field, string $value, string $boolean = 'and'): static
    {
        return $this->whereContains($field, $value, $boolean, true);
    }

    public function orWhereNotContains(string $field, string $value): static
    {
        return $this->whereNotContains($field, $value, 'or');
    }

    public function whereGeoRadius(
        float $lat,
        float $lng,
        float|int $distance,
        DistanceUnit $unit = DistanceUnit::Meters,
        ?int $resolution = null,
        string $boolean = 'and',
        bool $not = false,
    ): static {
        if ($distance < 0) {
            throw new InvalidArgumentException('A geo radius distance must not be negative');
        }

        if ($resolution !== null && ($resolution < 3 || $resolution > 1000)) {
            throw new InvalidArgumentException('A geo radius resolution must be between 3 and 1000');
        }

        $arguments = [$lat, $lng, $unit->toMeters($distance), ...($resolution === null ? [] : [$resolution])];
        $arguments = implode(', ', array_map($this->formatter()->number(...), $arguments));

        return $this->addFilter(\sprintf('_geoRadius(%s)', $arguments), $boolean, $not);
    }

    public function orWhereGeoRadius(
        float $lat,
        float $lng,
        float|int $distance,
        DistanceUnit $unit = DistanceUnit::Meters,
        ?int $resolution = null,
    ): static {
        return $this->whereGeoRadius($lat, $lng, $distance, $unit, $resolution, 'or');
    }

    public function whereNotGeoRadius(
        float $lat,
        float $lng,
        float|int $distance,
        DistanceUnit $unit = DistanceUnit::Meters,
        ?int $resolution = null,
        string $boolean = 'and',
    ): static {
        return $this->whereGeoRadius($lat, $lng, $distance, $unit, $resolution, $boolean, true);
    }

    public function orWhereNotGeoRadius(
        float $lat,
        float $lng,
        float|int $distance,
        DistanceUnit $unit = DistanceUnit::Meters,
        ?int $resolution = null,
    ): static {
        return $this->whereNotGeoRadius($lat, $lng, $distance, $unit, $resolution, 'or');
    }

    public function whereGeoBoundingBox(
        array $topRight,
        array $bottomLeft,
        string $boolean = 'and',
        bool $not = false,
    ): static {
        $format = $this->formatter();
        $filter = \sprintf('_geoBoundingBox(%s, %s)', $format->point($topRight), $format->point($bottomLeft));

        return $this->addFilter($filter, $boolean, $not);
    }

    public function orWhereGeoBoundingBox(array $topRight, array $bottomLeft): static
    {
        return $this->whereGeoBoundingBox($topRight, $bottomLeft, 'or');
    }

    public function whereNotGeoBoundingBox(array $topRight, array $bottomLeft, string $boolean = 'and'): static
    {
        return $this->whereGeoBoundingBox($topRight, $bottomLeft, $boolean, true);
    }

    public function orWhereNotGeoBoundingBox(array $topRight, array $bottomLeft): static
    {
        return $this->whereNotGeoBoundingBox($topRight, $bottomLeft, 'or');
    }

    public function whereGeoPolygon(array $points, string $boolean = 'and', bool $not = false): static
    {
        if (\count($points) < 3) {
            throw new InvalidArgumentException('A geo polygon filter requires at least three points');
        }

        $points = implode(', ', array_map($this->formatter()->point(...), $points));

        return $this->addFilter(\sprintf('_geoPolygon(%s)', $points), $boolean, $not);
    }

    public function orWhereGeoPolygon(array $points): static
    {
        return $this->whereGeoPolygon($points, 'or');
    }

    public function whereNotGeoPolygon(array $points, string $boolean = 'and'): static
    {
        return $this->whereGeoPolygon($points, $boolean, true);
    }

    public function orWhereNotGeoPolygon(array $points): static
    {
        return $this->whereNotGeoPolygon($points, 'or');
    }

    public function toFilter(): string
    {
        $expression = '';
        foreach ($this->filters as $index => $filter) {
            $expression .= ($index === 0 ? '' : ' ' . $filter['boolean'] . ' ') . $filter['filter'];
        }

        return $expression;
    }

    /**
     * Use `=` when only a value is given, and `!=` for the `<>` operator.
     *
     * @return array{mixed, mixed} The operator and value.
     */
    protected function prepareValueAndOperator(mixed $operator, mixed $value, bool $useDefault): array
    {
        [$operator, $value] = $useDefault ? ['=', $operator] : [$operator, $value];

        return [$operator === '<>' ? '!=' : $operator, $value];
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
     * Add a filter expression for a field, e.g. `rank IS NULL`.
     *
     * @return $this
     */
    protected function addFieldFilter(string $field, string $condition, string $boolean): static
    {
        return $this->addFilter($this->formatter()->field($field) . ' ' . $condition, $boolean);
    }

    /**
     * Get the formatter for fields and values.
     */
    protected function formatter(): FormatsFilterValues
    {
        return $this->formatter ??= resolve(FormatsFilterValues::class);
    }
}
