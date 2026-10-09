<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\ChecksHealth;
use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Tests\Models\BrokenMovie;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Meilisearch\Client;

/**
 * Test ChecksHealth::__invoke() method.
 */
test('invoke', function (): void {
    config(['scout.meilisearch.index-settings' => ['books' => ['sortableAttributes' => ['title']]]]);
    $meiliMovies = resolve(MeiliMovie::class)->searchableAs();
    $brokenMovies = resolve(BrokenMovie::class)->searchableAs();

    $this->withIndex($meiliMovies, function () use ($meiliMovies, $brokenMovies): void {
        $this->withIndex($brokenMovies, function () use ($meiliMovies, $brokenMovies): void {
            $client = resolve(Client::class);
            $client->waitForTask($client->createIndex($meiliMovies)['taskUid']);

            $health = resolve(ChecksHealth::class)();

            expect($health['version'])->toMatch('/^\d+\.\d+\.\d+/')
                ->and($health['missingIndexes'])->toBe(['testing-books'])
                ->and($health['outOfSync'])->toHaveKey($meiliMovies)
                ->and($health['outOfSync'][$meiliMovies])->toContain('rankingRules', 'filterableAttributes')
                ->and($health['errors'])->toBe([$brokenMovies => 'The distinct attribute field must be a string.'])
                ->and($health['failedTasks'][0]['error']['code'] ?? null)->toBe('index_already_exists')
                ->and($health['failedTaskCount'])->toBeGreaterThanOrEqual(1)
                ->and($health['error'])->toBeNull()
                ->and(array_keys(resolve(ListsIndexes::class)()))->not->toContain('testing-books')
            ;

            $withoutSettings = resolve(ChecksHealth::class)(0, false);

            expect($withoutSettings['outOfSync'])->toBeEmpty()
                ->and($withoutSettings['errors'])->toBeEmpty()
                ->and($withoutSettings['missingIndexes'])->toBe(['testing-books'])
            ;
        });
    });
});

/**
 * Test ChecksHealth::__invoke() method when MeiliSearch is unreachable.
 */
test('unreachable', function (): void {
    config(['scout.meilisearch.host' => 'http://localhost:7777']);
    app()->forgetInstance(Client::class);

    expect(resolve(ChecksHealth::class)())->toBe([
        'version'         => null,
        'error'           => null,
        'missingIndexes'  => [],
        'outOfSync'       => [],
        'errors'          => [],
        'failedTasks'     => [],
        'failedTaskCount' => 0,
    ]);
});

/**
 * Test ChecksHealth::__invoke() method when indexes can't be listed.
 */
test('without listing indexes', function (): void {
    app()->instance(ListsIndexes::class, new class implements ListsIndexes
    {
        public function __invoke(bool $stats = false): array
        {
            throw new RuntimeException('The provided API key is invalid.');
        }
    });

    $health = resolve(ChecksHealth::class)();

    expect($health['version'])->not->toBeNull()
        ->and($health['error'])->toBe('The provided API key is invalid.')
        ->and($health['missingIndexes'])->toBeEmpty()
    ;
});

/**
 * Test ChecksHealth::__invoke() method when tasks can't be listed.
 */
test('without listing tasks', function (): void {
    config(['scout.meilisearch.index-settings' => ['books' => []]]);
    $client = Mockery::mock(Client::class, [(string) config('scout.meilisearch.host'), config('scout.meilisearch.key')])
        ->makePartial()
    ;
    $client->shouldReceive('getTasks')->andThrow(new RuntimeException('Tasks unavailable'));
    app()->instance(Client::class, $client);

    $health = resolve(ChecksHealth::class)();

    expect($health['error'])->toBe('Tasks unavailable')
        ->and($health['missingIndexes'])->toContain('testing-books')
    ;
});
