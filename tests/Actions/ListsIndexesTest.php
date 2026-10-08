<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Illuminate\Testing\Fluent\AssertableJson;
use Meilisearch\Exceptions\CommunicationException;

/**
 * Test using wrong Scout driver.
 */
test('meili tools exception', function (): void {
    config(['scout.driver' => null]);

    resolve(ListsIndexes::class)();
})->throws(MeiliToolsException::class);

/**
 * Test getting index list when MeiliSearch isn't running.
 */
test('communication exception', function (): void {
    config(['scout.meilisearch.host' => 'http://localhost:7777']);

    resolve(ListsIndexes::class)();
})->throws(CommunicationException::class, 'Failed to connect to localhost');

/**
 * Test ListsIndexes::__invoke() method.
 */
test('invoke', function (): void {
    $this->withIndex('testing-indexes-list', function (): void {
        $list = resolve(ListsIndexes::class)();

        expect($list)->toHaveKey('testing-indexes-list');
        AssertableJson::fromArray($list['testing-indexes-list'])
            ->where('uid', 'testing-indexes-list')
            ->where('primaryKey', null)
            ->whereType('createdAt', 'string')
            ->whereType('updatedAt', 'string')
            ->interacted()
        ;
    });
});

/**
 * Test ListsIndexes::__invoke() method with stats.
 */
test('invoke with stats', function (): void {
    $this->withIndex('testing-indexes-list', function (): void {
        $list = resolve(ListsIndexes::class)(true);

        expect($list)->toHaveKey('testing-indexes-list');
        AssertableJson::fromArray($list['testing-indexes-list'])
            ->where('uid', 'testing-indexes-list')
            ->where('primaryKey', null)
            ->whereType('createdAt', 'string')
            ->whereType('updatedAt', 'string')
            ->where('numberOfDocuments', 0)
            ->where('isIndexing', false)
            ->etc()
            ->interacted()
        ;
    });
});
