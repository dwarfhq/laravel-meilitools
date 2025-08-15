<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Illuminate\Testing\Fluent\AssertableJson;
use MeiliSearch\Exceptions\CommunicationException;

/**
 * @internal
 */

/**
 * Test using wrong Scout driver.
 */
test('meili tools exception', function () {
    config(['scout.driver' => null]);

    $this->expectException(MeiliToolsException::class);

    $action = app()->make(ListsIndexes::class);
    $list = ($action)();
});

/**
 * Test getting index list when MeiliSearch isn't running.
 */
test('communication exception', function () {
    config(['scout.meilisearch.host' => 'http://localhost:7777']);

    $this->expectException(CommunicationException::class);
    $this->expectExceptionMessage('Failed to connect to localhost port 7777');

    $action = app()->make(ListsIndexes::class);
    $list = ($action)();
});

/**
 * Test ListsIndexes::__invoke() method.
 */
test('invoke', function () {
    $this->withIndex(self::INDEX, function () {
        $action = app()->make(ListsIndexes::class);
        $list = ($action)();

        $this->assertArrayHasKey(self::INDEX, $list);
        AssertableJson::fromArray($list[self::INDEX])
            ->where('uid', self::INDEX)
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
test('invoke with stats', function () {
    $this->withIndex(self::INDEX, function () {
        $action = app()->make(ListsIndexes::class);
        $list = ($action)(true);

        $this->assertArrayHasKey(self::INDEX, $list);
        AssertableJson::fromArray($list[self::INDEX])
            ->where('uid', self::INDEX)
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
