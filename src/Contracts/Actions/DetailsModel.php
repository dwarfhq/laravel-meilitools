<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Details model index.
 */
interface DetailsModel
{
    /**
     * Get extensive model index details.
     *
     * @param string $class Model class.
     */
    public function __invoke(string $class): array;
}
