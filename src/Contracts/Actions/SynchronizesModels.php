<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Synchronizes model indexes.
 */
interface SynchronizesModels
{
    /**
     * Synchronizes model index details.
     *
     * @param array         $classes  Model classes.
     * @param callable|null $callback Callback executed for each model.
     *
     * @return void
     */
    public function __invoke(array $classes, ?callable $callback = null): void;
}
