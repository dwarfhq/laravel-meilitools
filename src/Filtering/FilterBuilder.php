<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering;

use Dwarf\MeiliTools\Contracts\Filtering\FilterBuilder as Contract;
use Dwarf\MeiliTools\Contracts\Filtering\Filters;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

class FilterBuilder implements Contract
{
    protected array $filters = [];

    public function where(string $key, string $operator, $value, string $boolean = 'and'): Contract
    {
        $filter = app(Filters\BasicFilter::class, compact('key', 'value', 'operator'));
        $this->filters[] = compact('filter', 'boolean');

        return $this;
    }

    public function orWhere(string $key, string $operator, $value): Contract
    {
        return $this->where($key, $operator, $value, 'or');
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param string $key
     * @param mixed  $values
     * @param string $boolean
     * @param bool   $not
     *
     * @return $this
     */
    public function whereIn(string $key, $values, string $boolean = 'and', bool $not = false): Contract
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        $filter = app(Filters\GroupFilter::class, compact('key', 'values'));
        $this->filters[] = compact('filter', 'boolean', 'not');

        return $this;
    }

    /**
     * Add an "or where in" clause to the query.
     *
     * @param string $key
     * @param mixed  $values
     *
     * @return $this
     */
    public function orWhereIn(string $key, $values): Contract
    {
        return $this->whereIn($key, $values, 'or');
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param string $key
     * @param mixed  $values
     * @param string $boolean
     *
     * @return $this
     */
    public function whereNotIn(string $key, $values, string $boolean = 'and'): Contract
    {
        return $this->whereIn($key, $values, $boolean, true);
    }

    /**
     * Add an "or where not in" clause to the query.
     *
     * @param string $key
     * @param mixed  $values
     *
     * @return $this
     */
    public function orWhereNotIn($key, $values): Contract
    {
        return $this->whereNotIn($key, $values, 'or');
    }

    /**
     * Add a where between statement to the query.
     *
     * @param string   $key
     * @param iterable $values
     * @param string   $boolean
     * @param bool     $not
     *
     * @return $this
     */
    public function whereBetween(string $key, iterable $values, string $boolean = 'and', bool $not = false): Contract
    {
        [$low, $kigh] = collect($values)->all();
        $filter = app(Filters\BetweenFilter::class, compact('key', 'low', 'high'));
        $this->filters[] = compact('filter', 'boolean', 'not');

        return $this;
    }

    /**
     * Add an or where between statement to the query.
     *
     * @param string   $key
     * @param iterable $values
     *
     * @return $this
     */
    public function orWhereBetween(string $key, iterable $values): Contract
    {
        return $this->whereBetween($key, $values, 'or');
    }

    /**
     * Add a where not between statement to the query.
     *
     * @param string   $key
     * @param iterable $values
     * @param string   $boolean
     *
     * @return $this
     */
    public function whereNotBetween(string $key, iterable $values, string $boolean = 'and'): Contract
    {
        return $this->whereBetween($key, $values, $boolean, true);
    }

    /**
     * Add an or where not between statement to the query.
     *
     * @param string   $key
     * @param iterable $values
     *
     * @return $this
     */
    public function orWhereNotBetween(string $key, iterable $values): Contract
    {
        return $this->whereNotBetween($key, $values, 'or');
    }

    public function whereNested(Contract $filter, string $boolean = 'and'): Contract
    {
        $this->filters[] = compact('filter', 'boolean');

        return $this;
    }

    public function orWhereNested(Contract $filter): Contract
    {
        return $this->whereNested($filter, 'or');
    }

    public function whereGeoRadius(
        float $lat,
        float $lng,
        int $distance,
        string $boolean = 'and',
        bool $not = false
    ): Contract {
        $filter = app(Filters\GeoRadiusFilter::class, compact('lat', 'lng', 'distance'));
        $this->filters[] = compact('filter', 'boolean', 'not');

        return $this;
    }

    public function orWhereGeoRadius(float $lat, float $lng, int $distance): Contract
    {
        return $this->whereGeoRadius($lat, $lng, $distance, 'or');
    }

    public function whereNotGeoRadius(float $lat, float $lng, int $distance, string $boolean = 'and'): Contract
    {
        return $this->whereGeoRadius($lat, $lng, $distance, $boolean, true);
    }

    public function orWhereNotGeoRadius(float $lat, float $lng, int $distance): Contract
    {
        return $this->whereNotGeoRadius($lat, $lng, $distance, 'or');
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        $filter = '';

        foreach ($this->filters as $clause) {
            if (!empty($filter)) {
                $filter .= sprintf(' %s ', Str::upper($clause['boolean']));
            }
            if (isset($clause['not'])) {
                $filter .= 'NOT ';
            }
            $filter .= sprintf($clause['filter'] instanceof Contract ? '(%s)' : '%s', (string) $clause['filter']);
        }

        return $filter;
    }
}
