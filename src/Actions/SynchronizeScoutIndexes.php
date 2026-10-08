<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\SynchronizesScoutIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesScoutIndexes;
use Throwable;

/**
 * Synchronize indexes configured in Scout's MeiliSearch index settings.
 */
class SynchronizeScoutIndexes implements SynchronizesScoutIndexes
{
    public function __construct(protected SynchronizesScoutIndex $synchronizeScoutIndex)
    {
    }

    public function __invoke(array $indexes, ?callable $callback = null, bool $pretend = false): void
    {
        foreach ($indexes as $index) {
            try {
                $result = ($this->synchronizeScoutIndex)($index, $pretend);
            } catch (Throwable $e) {
                $result = $e;
            }

            if ($callback !== null) {
                $callback($index, $result);
            }
        }
    }
}
