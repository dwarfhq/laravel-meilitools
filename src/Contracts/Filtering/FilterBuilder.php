<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Filtering;

interface FilterBuilder
{
    public function where(string $key, string $operator, $value, string $boolean = 'and'): self;

    public function orWhere(string $key, string $operator, $value): self;

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
    public function whereIn(string $key, $values, string $boolean = 'and', bool $not = false): self;

    /**
     * Add an "or where in" clause to the query.
     *
     * @param string $key
     * @param mixed  $values
     *
     * @return $this
     */
    public function orWhereIn(string $key, $values): self;

    /**
     * Add a "where not in" clause to the query.
     *
     * @param string $key
     * @param mixed  $values
     * @param string $boolean
     *
     * @return $this
     */
    public function whereNotIn(string $key, $values, string $boolean = 'and'): self;

    /**
     * Add an "or where not in" clause to the query.
     *
     * @param string $key
     * @param mixed  $values
     *
     * @return $this
     */
    public function orWhereNotIn($key, $values): self;

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
    public function whereBetween(string $key, iterable $values, string $boolean = 'and', bool $not = false): self;

    /**
     * Add an or where between statement to the query.
     *
     * @param string   $key
     * @param iterable $values
     *
     * @return $this
     */
    public function orWhereBetween(string $key, iterable $values): self;

    /**
     * Add a where not between statement to the query.
     *
     * @param string   $key
     * @param iterable $values
     * @param string   $boolean
     *
     * @return $this
     */
    public function whereNotBetween(string $key, iterable $values, string $boolean = 'and'): self;

    /**
     * Add an or where not between statement to the query.
     *
     * @param string   $key
     * @param iterable $values
     *
     * @return $this
     */
    public function orWhereNotBetween(string $key, iterable $values): self;

    public function whereNested(self $filter, string $boolean = 'and'): self;

    public function orWhereNested(self $filter): self;

    public function whereGeoRadius(
        float $lat,
        float $lng,
        int $distance,
        string $boolean = 'and',
        bool $not = false
    ): self;

    public function orWhereGeoRadius(float $lat, float $lng, int $distance): self;

    public function whereNotGeoRadius(float $lat, float $lng, int $distance, string $boolean = 'and'): self;

    public function orWhereNotGeoRadius(float $lat, float $lng, int $distance): self;

    /**
     * Get filter string.
     *
     * @return string
     */
    public function __toString(): string;
}
