<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

use Illuminate\Database\Eloquent\Model;

/**
 * Synchronizes model index.
 */
interface SynchronizesModel
{
    /**
     * Synchronizes model index settings.
     *
     * @param class-string<Model> $class
     *
     * @return array<string, array{old: mixed, new: mixed}> Changes keyed by setting.
     */
    public function __invoke(string $class, bool $pretend = false): array;
}
