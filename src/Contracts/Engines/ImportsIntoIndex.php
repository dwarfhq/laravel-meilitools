<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Engines;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Scout engine able to import models into another index than their own.
 */
interface ImportsIntoIndex
{
    /**
     * Make the models searchable in the given index, the same way as in their own index.
     *
     * @param Collection<int, Model> $models
     */
    public function importInto(string $index, Collection $models): void;
}
