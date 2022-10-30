<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Views index.
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
