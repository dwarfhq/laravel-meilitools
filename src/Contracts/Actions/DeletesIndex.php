<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Deletes index.
 */
interface DeletesIndex
{
    /**
     * Delete index.
     *
     * @param string $index Index name.
     *
     * @return void
     */
    public function __invoke(string $index): void;
}
