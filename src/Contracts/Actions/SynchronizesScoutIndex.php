<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Synchronizes index configured in Scout's MeiliSearch index settings.
 */
interface SynchronizesScoutIndex
{
    /**
     * Synchronizes index settings from Scout's configuration.
     *
     * @return array<string, array{old: mixed, new: mixed}> Changes keyed by setting.
     */
    public function __invoke(string $index, bool $pretend = false): array;
}
