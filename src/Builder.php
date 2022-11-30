<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools;

use Closure;
use Illuminate\Support\Str;
use Laravel\Scout\Builder as BaseBuilder;

class Builder extends BaseBuilder
{
    /**
     * Filter builder.
     *
     * @var \Dwarf\MeiliTools\FilterBuilder
     */
    public FilterBuilder $builder;

    /**
     * {@inheritDoc}
     */
    public function __construct($model, $query, $callback = null, $softDelete = false)
    {
        parent::__construct($model, $query, $callback, $softDelete);

        $this->builder = new FilterBuilder();
    }

    public function where($field, $operator, $value = null, string $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        if ($field instanceof Closure && is_null($operator)) {
            return $this->whereNested($field, $boolean);
        )

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

    public function filter(): string
    {
        return (string) $this->builder;
    }

    /**
     * Proxy 'where' and 'orWhere' calls to filter builder.
     *
     * Will trigger undefined method error for all invalid calls.
     *
     * @param string $method
     * @param array  $args
     *
     * @return $this
     */
    public function __call(string $method, array $args)
    {
        if (method_exists($this->builder, $method) && Str::startsWith($method, ['where', 'orWhere'])) {
            return $this->builder->{$method}(...$args);
        }

        trigger_error('Call to undefined method ' . static::class . '::' . $method . '()', \E_USER_ERROR);
    }
}
