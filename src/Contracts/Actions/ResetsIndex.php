<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Resets index.
 */
interface ResetsIndex
{
    /**
     * Resets index settings.
     *
     * @param string $index Index name.
     *
     * @return array
     */
    public function __invoke(string $index): array;
}
