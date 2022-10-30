<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Views model index.
 */
interface ViewsModel
{
    /**
     * Get model index information.
     *
     * @param string $class Model class.
     *
     * @return array
     */
    public function __invoke(string $class): array;
}
