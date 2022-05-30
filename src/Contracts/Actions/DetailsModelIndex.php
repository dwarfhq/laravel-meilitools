<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Details model index.
 */
interface DetailsModelIndex
{
    /**
     * Get extensive model index details.
     *
     * @param string $class Model class.
     *
     * @return array
     */
    public function __invoke(string $class): array;
}
