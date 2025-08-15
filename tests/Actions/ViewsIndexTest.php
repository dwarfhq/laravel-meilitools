<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\ViewsIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Illuminate\Testing\Fluent\AssertableJson;
use MeiliSearch\Exceptions\ApiException;
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

    $action = app()->make(ViewsIndex::class);
    $info = ($action)(self::INDEX);
});

/**
 * Test getting index information when MeiliSearch isn't running.
 */
test('communication exception', function () {
    config(['scout.meilisearch.host' => 'http://localhost:7777']);

    $this->expectException(CommunicationException::class);
    $this->expectExceptionMessage('Failed to connect to localhost port 7777');

    $action = app()->make(ViewsIndex::class);
    $info = ($action)(self::INDEX);
});

/**
 * Test getting index information when it doesn't exist.
 */
test('api exception', function () {
    $this->expectException(ApiException::class);
    $this->expectExceptionMessage('Index `' . self::INDEX . '` not found.');

    $action = app()->make(ViewsIndex::class);
    $info = ($action)(self::INDEX);
});

/**
 * Test ViewsIndex::__invoke() method.
 */
test('invoke', function () {
    $this->withIndex(self::INDEX, function () {
        $action = app()->make(ViewsIndex::class);
        $info = ($action)(self::INDEX);

        AssertableJson::fromArray($info)
            ->where('uid', self::INDEX)
            ->where('primaryKey', null)
            ->whereType('createdAt', 'string')
            ->whereType('updatedAt', 'string')
            ->interacted()
        ;
    });
});

/**
 * Test ViewsIndex::__invoke() method with stats.
 */
test('invoke with stats', function () {
    $this->withIndex(self::INDEX, function () {
        $action = app()->make(ViewsIndex::class);
        $info = ($action)(self::INDEX, true);

        AssertableJson::fromArray($info)
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
