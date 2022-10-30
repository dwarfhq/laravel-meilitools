<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Lists index.
 */
interface ViewsIndex
{
    /**
     * Get index information.
     *
     * @param string $index Index name.
     *
     * @return array
     */
    public function __invoke(string $index): array;
}
