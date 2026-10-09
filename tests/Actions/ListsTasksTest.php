<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\ListsTasks;
use Meilisearch\Client;
use Meilisearch\Exceptions\ApiException;

/**
 * Test ListsTasks::__invoke() method.
 */
test('invoke', function (): void {
    $this->withIndex('testing-lists-tasks', function (): void {
        $client = resolve(Client::class);
        $client->waitForTask($client->createIndex('testing-lists-tasks')['taskUid']);

        // Tasks are kept after an index is deleted, so only the most recent ones are from this test.
        $tasks = resolve(ListsTasks::class)(['indexUids' => ['testing-lists-tasks']], 2);
        $failed = resolve(ListsTasks::class)(['indexUids' => ['testing-lists-tasks'], 'statuses' => ['failed']], 1);
        $created = resolve(ListsTasks::class)(
            ['indexUids' => ['testing-lists-tasks'], 'types' => ['indexCreation']],
            1,
        );

        expect(array_column($tasks, 'status'))->toBe(['failed', 'succeeded'])
            ->and($failed)->toHaveCount(1)
            ->and($failed[0]['error']['code'])->toBe('index_already_exists')
            ->and($created)->toHaveCount(1)
            ->and($created[0]['status'])->toBe('failed')
        ;
    });
});

/**
 * Test ListsTasks::__invoke() method with an invalid filter.
 */
test('invalid filter', function (): void {
    resolve(ListsTasks::class)(['statuses' => ['unknown']]);
})->throws(ApiException::class);
