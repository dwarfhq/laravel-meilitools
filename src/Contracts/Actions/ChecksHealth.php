<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Checks health.
 */
interface ChecksHealth
{
    /**
     * Check whether MeiliSearch is reachable, model and Scout configured indexes exist with their settings in sync,
     * and whether tasks failed recently.
     *
     * The version is null when MeiliSearch is unreachable, in which case nothing else is checked.
     * The error is set when MeiliSearch is reachable, but its indexes or tasks can't be listed, e.g. because the
     * API key lacks permissions, in which case the remaining checks are skipped.
     * Indexes are only checked, never created or changed.
     *
     * @return array{
     *     version: string|null,
     *     error: string|null,
     *     missingIndexes: list<string>,
     *     outOfSync: array<string, list<string>>,
     *     errors: array<string, string>,
     *     failedTasks: list<array<string, mixed>>,
     *     failedTaskCount: int,
     * } Settings out of sync and errors are keyed by index name. Failed tasks are the most recent ones, while the
     *   count includes every task failed within the given minutes.
     */
    public function __invoke(int $failedTasksWithinMinutes = 60, bool $checkSettings = true): array;
}
