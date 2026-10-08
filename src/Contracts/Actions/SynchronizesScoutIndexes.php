<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

use Throwable;

/**
 * Synchronizes indexes configured in Scout's MeiliSearch index settings.
 */
interface SynchronizesScoutIndexes
{
    /**
     * Synchronizes index settings from Scout's configuration.
     *
     * @param list<string> $indexes
     * @param (callable(
     *     string,
     *     array<string, array{old: mixed, new: mixed}>|Throwable,
     * ): void)|null $callback
     */
    public function __invoke(array $indexes, ?callable $callback = null, bool $pretend = false): void;
}
