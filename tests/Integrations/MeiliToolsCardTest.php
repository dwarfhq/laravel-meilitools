<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\ChecksHealth;
use Dwarf\MeiliTools\Contracts\Actions\ViewsStats;
use Dwarf\MeiliTools\Pulse\MeiliToolsCard;
use Livewire\Livewire;
use Meilisearch\Client;

/**
 * Bind a health action returning the given report, with nothing wrong otherwise.
 *
 * @param array<string, mixed> $report
 */
function fakeCardHealth(array $report): void
{
    app()->instance(ChecksHealth::class, new class($report) implements ChecksHealth
    {
        /**
         * @param array<string, mixed> $report
         */
        public function __construct(public array $report)
        {
        }

        public function __invoke(int $failedTasksWithinMinutes = 60, bool $checkSettings = true): array
        {
            return $this->report + [
                'version'         => '1.54.3',
                'error'           => null,
                'missingIndexes'  => [],
                'outOfSync'       => [],
                'errors'          => [],
                'failedTasks'     => [],
                'failedTaskCount' => 0,
            ];
        }
    });
}

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
    fakeCardHealth(['version' => null]);

    Livewire::withoutLazyLoading()->test(MeiliToolsCard::class)
        ->assertSee('unreachable')
        ->assertSee('Meilisearch could not be reached.')
    ;
});

/**
 * Test the Pulse card when indexes or tasks can't be listed.
 */
test('error', function (): void {
    fakeCardHealth(['error' => 'The provided API key is invalid.']);

    Livewire::withoutLazyLoading()->test(MeiliToolsCard::class)
        ->assertSee('v1.54.3')
        ->assertSee('Meilisearch could not be checked: The provided API key is invalid.')
    ;
});

/**
 * Test the Pulse card showing how many of the failed tasks are listed.
 */
test('failed task count', function (): void {
    fakeCardHealth(['failedTasks' => [['uid' => 7, 'type' => 'documentAdditionOrUpdate']], 'failedTaskCount' => 1200]);

    Livewire::withoutLazyLoading()->test(MeiliToolsCard::class)
        ->assertSeeInOrder(['(1 of', '1,200)'])
        ->assertSee('#7')
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
                'version'         => '1.54.3',
                'error'           => null,
                'missingIndexes'  => [],
                'outOfSync'       => [],
                'errors'          => [],
                'failedTasks'     => [],
                'failedTaskCount' => 0,
            ];
        }
    });

    Livewire::withoutLazyLoading()->test(MeiliToolsCard::class, ['failedTasksWithinMinutes' => 15, 'ttl' => 5])
        ->assertSet('ttl', 5)
        ->assertSee('v1.54.3')
    ;

    expect($minutes)->toBe(15);
});
