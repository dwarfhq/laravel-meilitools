<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering\Concerns;

use Dwarf\MeiliTools\Contracts\Filtering\SearchBuilder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;

/**
 * Applies the filters and search parameters of the search builder in a Scout MeiliSearch engine.
 *
 * Use it in a custom engine extending Scout's MeiliSearch engine to keep the search builder working.
 */
trait AppliesSearchBuilder
{
    /**
     * {@inheritDoc}
     *
     * Scout's filters, e.g. for soft deletes, are combined with the `filter` search option and the builder's filters.
     *
     * @param Builder<Model> $builder
     */
    protected function filters(Builder $builder)
    {
        $filters = parent::filters($builder);
        if (!$builder instanceof SearchBuilder) {
            return $filters;
        }

        $expressions = array_values(array_filter(
            [$filters, $this->optionFilter($builder->options['filter'] ?? null), $builder->toFilter()],
            fn (string $expression): bool => $expression !== '',
        ));

        if (\count($expressions) < 2) {
            return $expressions[0] ?? '';
        }

        $expressions = array_map(fn (string $expression): string => \sprintf('(%s)', $expression), $expressions);

        return implode(' AND ', $expressions);
    }

    /**
     * {@inheritDoc}
     *
     * @param Builder<Model>       $builder
     * @param array<string, mixed> $searchParams
     */
    protected function performSearch(Builder $builder, array $searchParams = [])
    {
        if ($builder instanceof SearchBuilder) {
            $searchParams = array_merge($builder->searchParameters(), $searchParams);
        }

        return parent::performSearch($builder, $searchParams);
    }

    /**
     * Convert a `filter` search option to an expression.
     *
     * MeiliSearch combines array filters with `AND`, and nested arrays with `OR`.
     */
    protected function optionFilter(mixed $filter): string
    {
        if (!\is_array($filter)) {
            return \is_string($filter) ? $filter : '';
        }

        $expressions = array_map(
            fn (mixed $expression): string => \is_array($expression)
                ? \sprintf('(%s)', implode(' OR ', array_map(strval(...), $expression)))
                : \sprintf('(%s)', (string) $expression),
            $filter,
        );

        return implode(' AND ', $expressions);
    }
}
