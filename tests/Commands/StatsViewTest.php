<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\ViewsStats;
use Meilisearch\Client;

/**
 * Test `meili:stats` command.
 */
test('stats', function (): void {
    $this->withIndex('testing-stats', function (): void {
        $client = resolve(Client::class);
        $task = $client->index('testing-stats')->addDocuments([['id' => 1, 'title' => 'Batman'], ['id' => 2]]);
        $client->waitForTask($task['taskUid']);

        $this->artisan('meili:stats')
            ->expectsOutputToContain('Database Size')
            ->expectsOutputToContain('testing-stats')
            ->assertSuccessful()
        ;

        $this->artisan('meili:stats', ['index' => 'testing-stats'])
            ->expectsOutputToContain('Number Of Documents')
            ->expectsOutputToContain('Is Indexing')
            ->expectsTable(['Field', 'Documents'], [['id', 2], ['title', 1]])
            ->assertSuccessful()
        ;
    });
});

/**
 * Test `meili:stats` command formatting stats which aren't plain values.
 */
test('stats formatting', function (): void {
    $stats = new class implements ViewsStats
    {
        public function __invoke(?string $index = null): array
        {
            return [
                'databaseSize' => 2048,
                'lastUpdate'   => null,
                'isIndexing'   => true,
                'embedders'    => ['default' => 3],
            ];
        }
    };
    app()->instance(ViewsStats::class, $stats);

    $this->artisan('meili:stats')
        ->expectsOutputToContain('2 KB')
        ->expectsOutputToContain('-')
        ->expectsOutputToContain('Yes')
        ->expectsOutputToContain("'default' => 3")
        ->assertSuccessful()
    ;
});
