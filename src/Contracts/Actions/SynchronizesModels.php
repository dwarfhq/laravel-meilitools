<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Synchronizes model indexes.
 */
interface SynchronizesModels
{
    /**
     * Synchronizes model index settings.
     *
     * @param list<class-string<Model>> $classes
     * @param (callable(
     *     class-string<Model>,
     *     array<string, array{old: mixed, new: mixed}>|Throwable,
     * ): void)|null $callback
     */
    public function __invoke(array $classes, ?callable $callback = null, bool $pretend = false): void;
}
