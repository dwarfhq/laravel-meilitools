<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\ChecksHealth;
use Dwarf\MeiliTools\Contracts\Actions\ViewsStats;
use Dwarf\MeiliTools\Pulse\MeiliToolsCard;
use Livewire\Livewire;
use Meilisearch\Client;

/**
 * Test the Pulse card showing indexes, problems and failed tasks.
 */
test('card', function (): void {
    $this->withIndex('testing-pulse', function (): void {
        $client = resolve(Client::class);
        $client->waitForTask($client->createIndex('testing-pulse')['taskUid']);
        config(['scout.meilisearch.index-settings' => ['books' => []]]);

        Livewire::withoutLazyLoading()->test(MeiliToolsCard::class)
            ->assertSee('Meilisearch')
            ->assertSee('testing-pulse')
            ->assertSee('Index <code>testing-books</code> is missing.', false)
            ->assertSee('Index `testing-pulse` already exists.')
        ;
    });
});

/**
 * Test the Pulse card when MeiliSearch is unreachable.
 */
test('unreachable', function (): void {
    app()->instance(ChecksHealth::class, new class implements ChecksHealth
    {
        public function __invoke(int $failedTasksWithinMinutes = 60, bool $checkSettings = true): array
        {
            return ['version' => null, 'missingIndexes' => [], 'outOfSync' => [], 'errors' => [], 'failedTasks' => []];
        }
    });

    Livewire::withoutLazyLoading()->test(MeiliToolsCard::class)
        ->assertSee('unreachable')
        ->assertSee('Meilisearch could not be reached.')
    ;
});

/**
 * Test the Pulse card when stats can't be read.
 */
test('without stats', function (): void {
    app()->instance(ViewsStats::class, new class implements ViewsStats
    {
        public function __invoke(?string $index = null): array
        {
            throw new RuntimeException('The provided API key is invalid.');
        }
    });

    Livewire::withoutLazyLoading()->test(MeiliToolsCard::class)
        ->assertSee('Meilisearch')
        ->assertDontSee('Documents')
    ;
});

/**
 * Test the Pulse card passing its attributes to the health check.
 */
test('attributes', function (): void {
    $minutes = null;
    app()->instance(ChecksHealth::class, new class($minutes) implements ChecksHealth
    {
        public function __construct(public mixed &$minutes)
        {
        }

        public function __invoke(int $failedTasksWithinMinutes = 60, bool $checkSettings = true): array
        {
            $this->minutes = $failedTasksWithinMinutes;

            return [
                'version'        => '1.54.3',
                'missingIndexes' => [],
                'outOfSync'      => [],
                'errors'         => [],
                'failedTasks'    => [],
            ];
        }
    });

    Livewire::withoutLazyLoading()->test(MeiliToolsCard::class, ['failedTasksWithinMinutes' => 15, 'ttl' => 5])
        ->assertSet('ttl', 5)
        ->assertSee('v1.54.3')
    ;

    expect($minutes)->toBe(15);
});
