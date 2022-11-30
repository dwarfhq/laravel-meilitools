<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools;

use Illuminate\Contracts\Support\Arrayable;

class FilterBuilder
{
    protected array $wheres = [];

    public function where(string $column, $operator = null, $value = null, string $boolean = 'and'): self
    {
        $type = 'Basic';

        $this->wheres[] = compact('type', 'column', 'operator', 'value', 'boolean');

        return $this;
    }

    public function orWhere(string $column, $operator = null, $value = null): self
    {
        return $this->where($column, $operator, $value, 'or');
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereIn(string $column, $values, string $boolean = 'and', bool $not = false): self
    {
        $type = $not ? 'NotIn' : 'In';

        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        $this->wheres[] = compact('type', 'column', 'values', 'boolean');

        return $this;
    }

    /**
     * Add an "or where in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed  $values
     * @return $this
     */
    public function orWhereIn(string $column, $values): self
    {
        return $this->whereIn($column, $values, 'or');
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotIn(string $column, $values, $boolean = 'and'): self
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Add an "or where not in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed  $values
     * @return $this
     */
    public function orWhereNotIn($column, $values): self
    {
        return $this->whereNotIn($column, $values, 'or');
    }

    /**
     * Add a where between statement to the query.
     *
     * @param  string  $column
     * @param  iterable  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereBetween(string $column, iterable $values, string $boolean = 'and', bool $not = false): self
    {
        return $this;
    }

    /**
     * Add an or where between statement to the query.
     *
     * @param  string  $column
     * @param  iterable  $values
     * @return $this
     */
    public function orWhereBetween(string $column, iterable $values): self
    {
        return $this->whereBetween($column, $values, 'or');
    }

    /**
     * Add a where not between statement to the query.
     *
     * @param  string  $column
     * @param  iterable  $values
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotBetween(string $column, iterable $values, string $boolean = 'and'): self
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }

    /**
     * Add an or where not between statement to the query.
     *
     * @param  string  $column
     * @param  iterable  $values
     * @return $this
     */
    public function orWhereNotBetween(string $column, iterable $values): self
    {
        return $this->whereNotBetween($column, $values, 'or');
    }

    public function whereNested(self $builder, string $boolean = 'and'): self
    {
        return $this;
    }

    public function orWhereNested(self $builder): self
    {
        return $this->whereNested($builder, 'or');
    }

    public function whereGeoRadius(float $lat, float $lng, int $distance, string $boolean = 'and', bool $not = false): self
    {
        return $this;
    }

    public function orWhereGeoRadius(float $lat, float $lng, int $distance): self
    {
        return $this->whereGeoRadius($lat, $lng, $distance, 'or');
    }

    public function whereNotGeoRadius(float $lat, float $lng, int $distance, string $boolean = 'and'): self
    {
        return $this->whereGeoRadius($lat, $lng, $distance, $boolean, true);
    }

    public function orWhereNotGeoRadius(float $lat, float $lng, int $distance): self
    {
        return $this->whereNotGeoRadius($lat, $lng, $distance, 'or');
    }

    public function __toString(): string
    {
        return '';
    }
}
