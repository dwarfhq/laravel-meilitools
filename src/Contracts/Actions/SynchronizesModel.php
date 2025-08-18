<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Synchronizes model index.
 */
interface SynchronizesModel
{
    /**
     * Synchronizes model index settings.
     *
     * @param string $class Model class.
     */
    public function __invoke(string $class): array;
}
