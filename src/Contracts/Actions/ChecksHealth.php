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
     * Indexes are only checked, never created or changed.
     *
     * @return array{
     *     version: string|null,
     *     missingIndexes: list<string>,
     *     outOfSync: array<string, list<string>>,
     *     errors: array<string, string>,
     *     failedTasks: list<array<string, mixed>>,
     * } Settings out of sync and errors are keyed by index name.
     */
    public function __invoke(int $failedTasksWithinMinutes = 60, bool $checkSettings = true): array;
}
