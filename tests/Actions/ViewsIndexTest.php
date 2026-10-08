<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\ViewsIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Illuminate\Testing\Fluent\AssertableJson;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Exceptions\CommunicationException;

/**
 * Test using wrong Scout driver.
 */
test('meili tools exception', function (): void {
    config(['scout.driver' => null]);

    resolve(ViewsIndex::class)('testing-views-index');
})->throws(MeiliToolsException::class);

/**
 * Test getting index information when MeiliSearch isn't running.
 */
test('communication exception', function (): void {
    config(['scout.meilisearch.host' => 'http://localhost:7777']);

    resolve(ViewsIndex::class)('testing-views-index');
})->throws(CommunicationException::class, 'Failed to connect to localhost');

/**
 * Test getting index information when it doesn't exist.
 */
test('api exception', function (): void {
    resolve(ViewsIndex::class)('testing-views-index');
})->throws(ApiException::class, 'Index `testing-views-index` not found.');

/**
 * Test ViewsIndex::__invoke() method.
 */
test('invoke', function (): void {
    $this->withIndex('testing-views-index', function (): void {
        $info = resolve(ViewsIndex::class)('testing-views-index');

        AssertableJson::fromArray($info)
            ->where('uid', 'testing-views-index')
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
test('invoke with stats', function (): void {
    $this->withIndex('testing-views-index', function (): void {
        $info = resolve(ViewsIndex::class)('testing-views-index', true);

        AssertableJson::fromArray($info)
            ->where('uid', 'testing-views-index')
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
