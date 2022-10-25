<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools;

use Laravel\Scout\Builder as BaseBuilder;

class Builder extends BaseBuilder
{
    /**
     * Filter builder.
     *
     * @var \Dwarf\MeiliTools\FilterBuilder
     */
    protected FilterBuilder $builder;

    /**
     * {@inheritDoc}
     */
    public function __construct($model, $query, $callback = null, $softDelete = false)
    {
        parent::__construct($model, $query, $callback, $softDelete);

        $this->builder = new FilterBuilder();
    }

    public function where($field, $operator, $value = null)
    {
        return $this;
    }

    public function whereIn($field, array $values)
    {
        return $this;
    }

    public function filter(): string
    {
        return (string) $this->builder;
    }
}
