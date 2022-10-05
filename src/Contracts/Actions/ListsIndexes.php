<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Indexes list.
 */
interface ListsIndexes
{
    /**
     * Get a list of all indexes.
     *
     * @return array
     */
    public function __invoke(): array;
}
