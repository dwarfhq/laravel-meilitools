<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering;

use Closure;
use Dwarf\MeiliTools\Contracts\Filtering\Builder as Contract;
use Dwarf\MeiliTools\Contracts\Filtering\FilterBuilder as Filterer;
use Illuminate\Support\Str;
use Laravel\Scout\Builder as BaseBuilder;

class Builder extends BaseBuilder implements Contract
{
    /**
     * Filter builder.
     *
     * @var \Dwarf\MeiliTools\Contracts\Filtering\FilterBuilder
     */
    public Filterer $builder;

    /**
     * {@inheritDoc}
     */
    public function __construct($model, $query, $callback = null, $softDelete = false)
    {
        parent::__construct($model, $query, $callback, $softDelete);

        $this->builder = app(Filterer::class);
    }

    public function where($field, $operator, $value = null, string $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        if ($field instanceof Closure && is_null($operator)) {
            return $this->whereNested($field, $boolean);
        }

        return $this;
    }

    public function orWhere($field, $operator = null, $value = null)
    {
        return $this->where($field, $operator, $value, 'or');
    }

    public function whereIn($field, array $values, string $boolean = 'and')
    {
        return $this;
    }

    public function orWhereIn($field, array $values)
    {
        return $this->whereIn($field, $values, 'or');
    }

    public function whereNested(Closure $callback, string $boolean = 'and')
    {
        // We only need the builder instance as a proxy, so only model is necessary.
        $builder = app(static::class, ['model' => $this->model, 'query' => null]);
        $callback($builder);

        $this->builder->whereNested($builder->builder, $boolean);

        return $this;
    }

    public function orWhereNested(Closure $callback)
    {
        return $this->whereNested($callback, 'or');
    }

    /**
     * {@inheritDoc}
     */
    public function filter(): string
    {
        return (string) $this->builder;
    }

    /**
     * {@inheritDoc}
     *
     * Proxy 'where' and 'orWhere' calls to filter builder.
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->builder, $method) && Str::startsWith($method, ['where', 'orWhere'])) {
            return $this->builder->{$method}(...$args);
        }

        return parent::__call($method, $parameters);
    }
}
