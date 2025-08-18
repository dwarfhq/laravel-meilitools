<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\CreatesIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Illuminate\Testing\Fluent\AssertableJson;
use MeiliSearch\Exceptions\CommunicationException;

/**
 * Test using wrong Scout driver.
 */
test('meili tools exception', function () {
    config(['scout.driver' => null]);

    app()->make(CreatesIndex::class)('testing-creates-index');
})->throws(MeiliToolsException::class);

/**
 * Test creating index when MeiliSearch isn't running.
 */
test('communication exception', function () {
    config(['scout.meilisearch.host' => 'http://localhost:7777']);

    app()->make(CreatesIndex::class)('testing-creates-index');
})->throws(CommunicationException::class, 'Failed to connect to localhost port 7777');

/**
 * Test CreatesIndex::__invoke() method.
 */
test('invoke', function () {
    try {
        $info = app()->make(CreatesIndex::class)('testing-creates-index');

        AssertableJson::fromArray($info)
            ->where('uid', 'testing-creates-index')
            ->where('primaryKey', null)
            ->whereType('createdAt', 'string')
            ->whereType('updatedAt', 'string')
            ->interacted()
        ;
    } finally {
        $this->deleteIndex('testing-creates-index');
    }
});

/**
 * Test CreatesIndex::__invoke() method with options.
 */
test('invoke with options', function () {
    try {
        $info = app()->make(CreatesIndex::class)('testing-creates-index', ['primaryKey' => 'id']);

        AssertableJson::fromArray($info)
            ->where('uid', 'testing-creates-index')
            ->where('primaryKey', 'id')
            ->whereType('createdAt', 'string')
            ->whereType('updatedAt', 'string')
            ->interacted()
        ;
    } finally {
        $this->deleteIndex('testing-creates-index');
    }
});
