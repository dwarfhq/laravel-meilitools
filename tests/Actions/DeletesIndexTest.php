<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DeletesIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Meilisearch\Exceptions\CommunicationException;

/**
 * Test using wrong Scout driver.
 */
test('meili tools exception', function (): void {
    config(['scout.driver' => null]);

    resolve(DeletesIndex::class)('testing-deletes-index');
})->throws(MeiliToolsException::class);

/**
 * Test deleting index when MeiliSearch isn't running.
 */
test('communication exception', function (): void {
    config(['scout.meilisearch.host' => 'http://localhost:7777']);

    resolve(DeletesIndex::class)('testing-deletes-index');
})->throws(CommunicationException::class, 'Failed to connect to localhost');

/**
 * Test deleting index when it doesn't exist.
 */
test('index missing', function (): void {
    // No errors will be thrown in this case.
    resolve(DeletesIndex::class)('testing-deletes-index');
})->throwsNoExceptions();

/**
 * Test DeletesIndex::__invoke() method.
 */
test('invoke', function (): void {
    $this->createIndex('testing-deletes-index');
    resolve(DeletesIndex::class)('testing-deletes-index');
})->throwsNoExceptions();
