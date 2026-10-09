<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\CancelsTasks;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Meilisearch\Client;

/**
 * Test CancelsTasks::__invoke() method with finished tasks, which can't be canceled.
 */
test('invoke', function (): void {
    $this->withIndex('testing-cancels-tasks', function (): void {
        $task = resolve(Client::class)->getTasks()->getResults()[0];

        expect(resolve(CancelsTasks::class)(['uids' => [$task['uid']]]))->toBe(0)
            ->and(resolve(CancelsTasks::class)(['indexUids' => ['testing-cancels-tasks'], 'statuses' => ['enqueued']]))
            ->toBe(0)
        ;
    });
});

/**
 * Test CancelsTasks::__invoke() method without filters.
 */
test('without filters', function (): void {
    resolve(CancelsTasks::class)(['statuses' => [], 'uids' => []]);
})->throws(MeiliToolsException::class, 'At least one filter is required to cancel tasks');
