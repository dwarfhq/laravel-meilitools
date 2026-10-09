<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions\Concerns;

use Meilisearch\Contracts\CancelTasksQuery;
use Meilisearch\Contracts\TasksQuery;

trait BuildsTaskQueries
{
    /**
     * Apply the filters to a task query.
     *
     * @template TQuery of CancelTasksQuery|TasksQuery
     *
     * @param TQuery                                                                                           $query
     * @param array{statuses?: list<string>, types?: list<string>, indexUids?: list<string>, uids?: list<int>} $filters
     *
     * @return TQuery
     */
    protected function applyTaskFilters(CancelTasksQuery|TasksQuery $query, array $filters): CancelTasksQuery|TasksQuery
    {
        if (($filters['statuses'] ?? []) !== []) {
            $query->setStatuses($filters['statuses']);
        }
        if (($filters['types'] ?? []) !== []) {
            $query->setTypes($filters['types']);
        }
        if (($filters['indexUids'] ?? []) !== []) {
            $query->setIndexUids($filters['indexUids']);
        }
        if (($filters['uids'] ?? []) !== []) {
            $query->setUids($filters['uids']);
        }

        return $query;
    }
}
