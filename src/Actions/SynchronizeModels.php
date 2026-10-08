<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModels;
use Throwable;

/**
 * Synchronize model indexes.
 */
class SynchronizeModels implements SynchronizesModels
{
    public function __construct(protected SynchronizesModel $synchronizeModel)
    {
    }

    public function __invoke(array $classes, ?callable $callback = null, bool $pretend = false): void
    {
        foreach ($classes as $class) {
            try {
                $result = ($this->synchronizeModel)($class, $pretend);
            } catch (Throwable $e) {
                $result = $e;
            }

            if ($callback !== null) {
                $callback($class, $result);
            }
        }
    }
}
