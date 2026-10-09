<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

use Illuminate\Database\Eloquent\Model;

/**
 * Reindexes model without downtime.
 */
interface ReindexesModel
{
    /**
     * Import all models into a temporary index with the model settings, then swap it with the model index.
     *
     * Searches keep using the existing index until the swap, but changes made to it while importing are lost.
     *
     * @param class-string<Model>        $class
     * @param (callable(int): void)|null $progress Called with the number of imported models.
     *
     * @return int The number of imported models.
     */
    public function __invoke(string $class, ?int $chunk = null, ?callable $progress = null, int $timeout = 300): int;
}
