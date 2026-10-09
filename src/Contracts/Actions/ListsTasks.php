<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Lists tasks.
 */
interface ListsTasks
{
    /**
     * Get the most recent tasks matching the filters.
     *
     * @param array{
     *     statuses?: list<string>,
     *     types?: list<string>,
     *     indexUids?: list<string>,
     *     afterFinishedAt?: \DateTimeInterface,
     * } $filters
     *
     * @return list<array<string, mixed>>
     */
    public function __invoke(array $filters = [], int $limit = 20): array;
}
