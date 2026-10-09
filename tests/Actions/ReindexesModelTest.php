<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Contracts\Actions\ReindexesModel;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Tests\Fixtures\InvalidMovie;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Dwarf\MeiliTools\Tests\Tools;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\MeilisearchEngine;
use Meilisearch\Client;

/**
 * Create a movies table with the given movies, without indexing them.
 *
 * @param class-string<Movie> $class
 * @param list<string>        $names
 */
function createMovies(string $class, array $names): void
{
    $model = new $class();
    Schema::create($model->getTable(), function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('description')->nullable();
        $table->string('keywords')->nullable();
        $table->integer('rating')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    $class::withoutSyncingToSearch(function () use ($class, $names): void {
        foreach ($names as $name) {
            $class::create(['name' => $name]);
        }
    });
}

/**
 * Get the document ids of an index.
 *
 * @return list<int>
 */
function documentIds(string $index): array
{
    $ids = array_column(resolve(Client::class)->index($index)->getDocuments()->getResults(), 'id');
    sort($ids);

    return $ids;
}

/**
 * Add a stale document to an index.
 */
function addStaleDocument(string $index): void
{
    $client = resolve(Client::class);
    $client->waitForTask($client->index($index)->addDocuments([['id' => 999, 'name' => 'Stale']], 'id')['taskUid']);
}

/**
 * Test ReindexesModel::__invoke() method.
 */
test('invoke', function (): void {
    createMovies(Movie::class, ['Batman', 'Superman', 'Robin']);

    $this->withIndex('testing-movies', function (): void {
        addStaleDocument('testing-movies');

        $progress = [];
        $imported = resolve(ReindexesModel::class)(Movie::class, 2, function (int $imported) use (&$progress): void {
            $progress[] = $imported;
        });

        expect($imported)->toBe(3)
            ->and($progress)->toBe([2, 3])
            ->and(documentIds('testing-movies'))->toBe([1, 2, 3])
            ->and(array_keys(resolve(ListsIndexes::class)()))->not->toContain('testing-movies_reindex')
        ;
    });
});

/**
 * Test ReindexesModel::__invoke() method with settings and soft deletes.
 */
test('with settings and soft deletes', function (): void {
    config(['scout.soft_delete' => true]);
    createMovies(MeiliMovie::class, ['Batman', 'Superman']);
    MeiliMovie::withoutSyncingToSearch(fn () => MeiliMovie::find(2)?->delete());

    try {
        expect(resolve(ReindexesModel::class)(MeiliMovie::class))->toBe(2);

        $documents = resolve(Client::class)->index('testing-meili_movies')->getDocuments()->getResults();
        $details = resolve(DetailsIndex::class)('testing-meili_movies');
        $settings = Tools::movieSettings();

        expect(array_column($documents, '__soft_deleted', 'id'))->toEqual([1 => 0, 2 => 1])
            ->and($details['filterableAttributes'])->toBe(['__soft_deleted', ...$settings['filterableAttributes']])
            ->and($details['rankingRules'])->toBe($settings['rankingRules'])
        ;
    } finally {
        $this->deleteIndex('testing-meili_movies');
    }
});

/**
 * Test ReindexesModel::__invoke() method with a missing model index.
 */
test('with missing index', function (): void {
    createMovies(Movie::class, ['Batman']);

    try {
        expect(resolve(ReindexesModel::class)(Movie::class))->toBe(1)
            ->and(documentIds('testing-movies'))->toBe([1])
        ;
    } finally {
        $this->deleteIndex('testing-movies');
    }
});

/**
 * Test ReindexesModel::__invoke() method with documents failing to import.
 */
test('with failed import', function (): void {
    createMovies(InvalidMovie::class, ['Batman']);

    $this->withIndex('testing-invalid_movies', function (): void {
        addStaleDocument('testing-invalid_movies');

        expect(fn () => resolve(ReindexesModel::class)(InvalidMovie::class))
            ->toThrow(MeiliToolsException::class, "Importing into 'testing-invalid_movies_reindex' failed")
            ->and(documentIds('testing-invalid_movies'))->toBe([999])
            ->and(array_keys(resolve(ListsIndexes::class)()))->not->toContain('testing-invalid_movies_reindex')
        ;
    });
});

/**
 * Test ReindexesModel::__invoke() method with an engine which can't import into another index.
 */
test('with unsupported engine', function (): void {
    resolve(EngineManager::class)->extend(
        'meilisearch',
        fn ($app): MeilisearchEngine => new MeilisearchEngine($app->make(Client::class)),
    );

    resolve(ReindexesModel::class)(Movie::class);
})->throws(
    MeiliToolsException::class,
    "The Scout engine must implement 'Dwarf\\MeiliTools\\Contracts\\Engines\\ImportsIntoIndex' to reindex",
);
