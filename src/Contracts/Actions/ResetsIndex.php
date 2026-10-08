<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Resets index.
 */
interface ResetsIndex
{
    /**
     * Reset index settings to defaults.
     *
     * @return array<string, array{old: mixed, new: mixed}> Changes keyed by setting.
     */
    public function __invoke(string $index, bool $pretend = false): array;
}
