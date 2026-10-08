<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Synchronizes index.
 */
interface SynchronizesIndex
{
    /**
     * Synchronizes index settings.
     *
     * @param array<string, mixed> $settings
     *
     * @return array<string, array{old: mixed, new: mixed}> Changes keyed by setting.
     */
    public function __invoke(string $index, array $settings, bool $pretend = false): array;
}
