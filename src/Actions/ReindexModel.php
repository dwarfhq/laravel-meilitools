<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Contracts\Actions\ReindexesModel;
use Dwarf\MeiliTools\Contracts\Actions\ResolvesModelSettings;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Contracts\Engines\ImportsIntoIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Meilisearch\Client;
use Meilisearch\Contracts\TasksQuery;
use Meilisearch\Exceptions\CommunicationException;
use Meilisearch\Exceptions\TimeOutException;

/**
 * Reindex model without downtime.
 */
class ReindexModel implements ReindexesModel
{
    public function __construct(
        protected Client $client,
        protected EnsuresIndexExists $ensureIndexExists,
        protected ResolvesModelSettings $resolveSettings,
        protected SynchronizesIndex $synchronizeIndex,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws MeiliToolsException    When not using MeiliSearch, the engine can't import into another index,
     *                                or importing failed.
     * @throws ValidationException    When the model settings are invalid.
     * @throws CommunicationException When connection to MeiliSearch fails.
     * @throws TimeOutException       When a task doesn't finish within the timeout in seconds.
     */
    public function __invoke(string $class, ?int $chunk = null, ?callable $progress = null, int $timeout = 300): int
    {
        Helpers::throwUnlessMeiliSearch();

        $index = Helpers::modelIndexName($class);
        $model = resolve($class);
        $engine = $model instanceof Model && method_exists($model, 'searchableUsing')
            ? $model->searchableUsing()
            : null;
        if (
            !$model instanceof Model
            || !$engine instanceof ImportsIntoIndex
            || !method_exists($model, 'getScoutKeyName')
        ) {
            throw new MeiliToolsException(
                \sprintf("The Scout engine must implement '%s' to reindex", ImportsIntoIndex::class),
            );
        }

        $primaryKey = $model->getScoutKeyName();
        $temporary = $index . '_reindex';

        $this->wait($this->client->deleteIndex($temporary), $timeout);
        $created = $this->wait($this->client->createIndex($temporary, ['primaryKey' => $primaryKey]), $timeout);

        try {
            ($this->synchronizeIndex)($temporary, ($this->resolveSettings)($class));
            $imported = $this->import($model, $engine, $temporary, $chunk, $progress);
            $this->waitForIndex($temporary, $timeout);
            $this->throwIfFailed($temporary, (int) $created['uid']);

            ($this->ensureIndexExists)($index, ['primaryKey' => $primaryKey]);
            $this->wait($this->client->swapIndexes([[$index, $temporary]]), $timeout);
        } finally {
            // After swapping, the temporary index contains the previous documents.
            $this->wait($this->client->deleteIndex($temporary), $timeout);
        }

        return $imported;
    }

    /**
     * Import the models into the index the same way as Scout's import, but synchronously.
     *
     * @param (callable(int): void)|null $progress
     */
    protected function import(
        Model $model,
        ImportsIntoIndex $engine,
        string $index,
        ?int $chunk,
        ?callable $progress,
    ): int {
        if (!method_exists($model, 'makeAllSearchableQuery') || !method_exists($model, 'getScoutKeyName')) {
            return 0;
        }

        $imported = 0;
        $query = $model->makeAllSearchableQuery();
        $keyName = $model->getScoutKeyName();

        $query->chunkById(
            $chunk ?? (int) config('scout.chunk.searchable', 500),
            function (Collection $models) use ($engine, $index, $progress, &$imported): void {
                $models = $models->filter(fn (Model $model): bool => !method_exists($model, 'shouldBeSearchable')
                    || (bool) $model->shouldBeSearchable())->values();

                $first = $models->first();
                if ($first !== null && method_exists($first, 'makeSearchableUsing')) {
                    /** @var Collection<int, Model> $models */
                    $models = $first->makeSearchableUsing($models);
                    $engine->importInto($index, $models);
                    $imported += $models->count();
                }

                if ($progress !== null) {
                    $progress($imported);
                }
            },
            $query->qualifyColumn($keyName),
            $keyName,
        );

        return $imported;
    }

    /**
     * Wait for the latest task of the index to finish.
     */
    protected function waitForIndex(string $index, int $timeout): void
    {
        $tasks = $this->client->getTasks(new TasksQuery()->setIndexUids([$index])->setLimit(1))->getResults();
        if (isset($tasks[0]['uid'])) {
            $this->client->waitForTask($tasks[0]['uid'], $timeout * 1000);
        }
    }

    /**
     * Throw if a task of the index failed after the given task.
     *
     * @throws MeiliToolsException
     */
    protected function throwIfFailed(string $index, int $after): void
    {
        $query = new TasksQuery()->setIndexUids([$index])->setStatuses(['failed']);
        foreach ($this->client->getTasks($query)->getResults() as $task) {
            if ($task['uid'] > $after) {
                throw new MeiliToolsException(\sprintf(
                    "Importing into '%s' failed: %s",
                    $index,
                    $task['error']['message'] ?? 'unknown error',
                ));
            }
        }
    }

    /**
     * Wait for a task to finish.
     *
     * @param array<string, mixed> $task
     *
     * @return array<string, mixed>
     */
    protected function wait(array $task, int $timeout): array
    {
        return $this->client->waitForTask($task['taskUid'], $timeout * 1000);
    }
}
