<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Lists indexes.
 */
interface ListsIndexes
{
    /**
     * Get a list of all indexes.
     *
     * @return array<string, array<string, mixed>> Index information keyed by index name.
     */
    public function __invoke(bool $stats = false): array;
}
