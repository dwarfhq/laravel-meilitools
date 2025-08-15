<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\CreatesIndex;
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

    $action = app()->make(CreatesIndex::class);
    $info = ($action)(self::INDEX);
});

/**
 * Test creating index when MeiliSearch isn't running.
 */
test('communication exception', function () {
    config(['scout.meilisearch.host' => 'http://localhost:7777']);

    $this->expectException(CommunicationException::class);
    $this->expectExceptionMessage('Failed to connect to localhost port 7777');

    $action = app()->make(CreatesIndex::class);
    $info = ($action)(self::INDEX);
});

/**
 * Test CreatesIndex::__invoke() method.
 */
test('invoke', function () {
    try {
        $action = app()->make(CreatesIndex::class);
        $info = ($action)(self::INDEX);

        AssertableJson::fromArray($info)
            ->where('uid', self::INDEX)
            ->where('primaryKey', null)
            ->whereType('createdAt', 'string')
            ->whereType('updatedAt', 'string')
            ->interacted()
        ;
    } finally {
        $this->deleteIndex(self::INDEX);
    }
});

/**
 * Test CreatesIndex::__invoke() method with options.
 */
test('invoke with options', function () {
    try {
        $action = app()->make(CreatesIndex::class);
        $info = ($action)(self::INDEX, ['primaryKey' => 'id']);

        AssertableJson::fromArray($info)
            ->where('uid', self::INDEX)
            ->where('primaryKey', 'id')
            ->whereType('createdAt', 'string')
            ->whereType('updatedAt', 'string')
            ->interacted()
        ;
    } finally {
        $this->deleteIndex(self::INDEX);
    }
});
