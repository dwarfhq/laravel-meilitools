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
     * @param string $index    Index name.
     * @param array  $settings Index settings.
     */
    public function __invoke(string $index, array $settings): array;
}
