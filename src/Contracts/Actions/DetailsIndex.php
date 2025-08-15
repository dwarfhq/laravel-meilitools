<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Details index.
 */
interface DetailsIndex
{
    /**
     * Get extensive index details.
     *
     * @param string $index Index name.
     */
    public function __invoke(string $index): array;
}
