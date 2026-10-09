<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ChecksHealth;
use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Contracts\Actions\ListsModels;
use Dwarf\MeiliTools\Contracts\Actions\ListsTasks;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesScoutIndex;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Support\Facades\Date;
use Throwable;

/**
 * Check health.
 */
class CheckHealth implements ChecksHealth
{
    public function __construct(
        protected ListsIndexes $listIndexes,
        protected ListsModels $listModels,
        protected ListsTasks $listTasks,
        protected SynchronizesModel $synchronizeModel,
        protected SynchronizesScoutIndex $synchronizeScoutIndex,
    ) {
    }

    public function __invoke(int $failedTasksWithinMinutes = 60, bool $checkSettings = true): array
    {
        $report = [
            'version'        => Helpers::engineVersion(),
            'missingIndexes' => [],
            'outOfSync'      => [],
            'errors'         => [],
            'failedTasks'    => [],
        ];
        if ($report['version'] === null) {
            return $report;
        }

        $existing = array_keys(($this->listIndexes)());

        // Settings are compared by synchronizing while pretending, only for existing indexes, so nothing is created.
        $indexes = [];
        foreach (array_filter(($this->listModels)(), Helpers::isSearchableModel(...)) as $class) {
            $indexes[Helpers::modelIndexName($class)] = fn (): array => ($this->synchronizeModel)($class, true);
        }
        foreach (array_keys(Helpers::scoutIndexes()) as $index) {
            $indexes[$index] ??= fn (): array => ($this->synchronizeScoutIndex)($index, true);
        }

        foreach ($indexes as $index => $synchronize) {
            if (!\in_array($index, $existing, true)) {
                $report['missingIndexes'][] = $index;

                continue;
            }

            if (!$checkSettings) {
                continue;
            }

            try {
                $changes = $synchronize();
            } catch (Throwable $e) {
                $report['errors'][$index] = $e->getMessage();

                continue;
            }

            if ($changes !== []) {
                $report['outOfSync'][$index] = array_keys($changes);
            }
        }

        $report['failedTasks'] = ($this->listTasks)([
            'statuses'        => ['failed'],
            'afterFinishedAt' => Date::now()->subMinutes($failedTasksWithinMinutes),
        ]);

        return $report;
    }
}
