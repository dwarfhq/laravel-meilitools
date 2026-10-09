<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Console\Commands\TasksList;
use Meilisearch\Client;

/**
 * Test `meili:tasks` command.
 */
test('tasks', function (): void {
    $this->withIndex('testing-tasks', function (): void {
        $client = resolve(Client::class);
        $client->waitForTask($client->createIndex('testing-tasks')['taskUid']);

        // Each expectation matches a separate row, as matched output lines are only used once.
        $this->artisan('meili:tasks', ['--index' => ['testing-tasks'], '--limit' => 2])
            ->expectsOutputToContain('succeeded')
            ->expectsOutputToContain('Index `testing-tasks` already exists.')
            ->assertSuccessful()
        ;

        $this->artisan('meili:tasks', ['--index' => ['testing-tasks'], '--status' => ['failed'], '--limit' => 1])
            ->expectsOutputToContain('Index `testing-tasks` already exists.')
            ->doesntExpectOutputToContain('succeeded')
            ->assertSuccessful()
        ;
    });
});

/**
 * Test formatting task durations.
 */
test('durations', function (string $duration, string $expected): void {
    $command = new class extends TasksList
    {
        public function duration(string $duration): string
        {
            return $this->formatDuration($duration);
        }
    };

    expect($command->duration($duration))->toBe($expected);
})->with([
    'milliseconds' => ['PT0.024051S', '24 ms'],
    'seconds'      => ['PT1.54S', '1.5 s'],
    'minutes'      => ['PT1M30.5S', '1m 30s'],
    'days'         => ['P1DT2H', '1d 2h'],
    'unknown'      => ['soon', 'soon'],
]);
