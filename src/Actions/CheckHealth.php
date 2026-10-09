<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ChecksHealth;
use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Contracts\Actions\ListsModels;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesScoutIndex;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Support\Facades\Date;
use Meilisearch\Client;
use Meilisearch\Contracts\TasksQuery;
use Throwable;

/**
 * Check health.
 */
class CheckHealth implements ChecksHealth
{
    /**
     * Maximum number of failed tasks included in the report.
     */
    protected const int FAILED_TASKS_LIMIT = 20;

    public function __construct(
        protected Client $client,
        protected ListsIndexes $listIndexes,
        protected ListsModels $listModels,
        protected SynchronizesModel $synchronizeModel,
        protected SynchronizesScoutIndex $synchronizeScoutIndex,
    ) {
    }

    public function __invoke(int $failedTasksWithinMinutes = 60, bool $checkSettings = true): array
    {
        $report = [
            'version'         => Helpers::engineVersion(),
            'error'           => null,
            'missingIndexes'  => [],
            'outOfSync'       => [],
            'errors'          => [],
            'failedTasks'     => [],
            'failedTaskCount' => 0,
        ];
        if ($report['version'] === null) {
            return $report;
        }

        try {
            $existing = array_keys(($this->listIndexes)());
        } catch (Throwable $e) {
            return ['error' => $e->getMessage()] + $report;
        }

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

        $query = new TasksQuery()
            ->setStatuses(['failed'])
            ->setAfterFinishedAt(Date::now()->subMinutes($failedTasksWithinMinutes))
            ->setLimit(self::FAILED_TASKS_LIMIT)
        ;

        try {
            $tasks = $this->client->getTasks($query);
        } catch (Throwable $e) {
            return ['error' => $e->getMessage()] + $report;
        }

        $report['failedTasks'] = array_values($tasks->getResults());
        $report['failedTaskCount'] = $tasks->getTotal();

        return $report;
    }
}
