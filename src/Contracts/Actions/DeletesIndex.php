<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Details index.
 */
interface DeletesIndex
{
    /**
     * Delete index.
     *
     * @param  string  $index Index name.
     * @return array
     */
    public function __invoke(string $index): array;
}
