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
        'version'        => null,
        'missingIndexes' => [],
        'outOfSync'      => [],
        'errors'         => [],
        'failedTasks'    => [],
    ]);
});
