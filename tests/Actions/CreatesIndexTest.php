<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\CreatesIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Illuminate\Testing\Fluent\AssertableJson;
use Meilisearch\Exceptions\CommunicationException;

/**
 * Test using wrong Scout driver.
 */
test('meili tools exception', function (): void {
    config(['scout.driver' => null]);

    resolve(CreatesIndex::class)('testing-creates-index');
})->throws(MeiliToolsException::class);

/**
 * Test creating index when MeiliSearch isn't running.
 */
test('communication exception', function (): void {
    config(['scout.meilisearch.host' => 'http://localhost:7777']);

    resolve(CreatesIndex::class)('testing-creates-index');
})->throws(CommunicationException::class, 'Failed to connect to localhost');

/**
 * Test CreatesIndex::__invoke() method.
 */
test('invoke', function (): void {
    try {
        $info = resolve(CreatesIndex::class)('testing-creates-index');

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
test('invoke with options', function (): void {
    try {
        $info = resolve(CreatesIndex::class)('testing-creates-index', ['primaryKey' => 'id']);

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
