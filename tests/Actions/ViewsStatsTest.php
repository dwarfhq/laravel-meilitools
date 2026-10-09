<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\ViewsStats;
use Meilisearch\Client;
use Meilisearch\Exceptions\ApiException;

/**
 * Test ViewsStats::__invoke() method.
 */
test('invoke', function (): void {
    $this->withIndex('testing-views-stats', function (): void {
        $client = resolve(Client::class);
        $task = $client->index('testing-views-stats')->addDocuments([['id' => 1, 'title' => 'Batman'], ['id' => 2]]);
        $client->waitForTask($task['taskUid']);

        $instance = resolve(ViewsStats::class)();
        $index = resolve(ViewsStats::class)('testing-views-stats');

        expect($instance)->toHaveKeys(['databaseSize', 'indexes.testing-views-stats'])
            ->and($index)->toMatchArray(['numberOfDocuments' => 2, 'isIndexing' => false])
            ->and($index['fieldDistribution'])->toEqual(['id' => 2, 'title' => 1])
        ;
    });
});

/**
 * Test ViewsStats::__invoke() method with a missing index.
 */
test('missing index', function (): void {
    resolve(ViewsStats::class)('testing-missing-index');
})->throws(ApiException::class);
