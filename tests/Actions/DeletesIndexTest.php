<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DeletesIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use MeiliSearch\Exceptions\CommunicationException;

/**
 * Test using wrong Scout driver.
 */
test('meili tools exception', function () {
    config(['scout.driver' => null]);

    app()->make(DeletesIndex::class)('testing-deletes-index');
})->throws(MeiliToolsException::class);

/**
 * Test deleting index when MeiliSearch isn't running.
 */
test('communication exception', function () {
    config(['scout.meilisearch.host' => 'http://localhost:7777']);

    app()->make(DeletesIndex::class)('testing-deletes-index');
})->throws(CommunicationException::class, 'Failed to connect to localhost port 7777');

/**
 * Test deleting index when it doesn't exist.
 */
test('index missing', function () {
    // No errors will be thrown in this case.
    app()->make(DeletesIndex::class)('testing-deletes-index');
})->throwsNoExceptions();

/**
 * Test DeletesIndex::__invoke() method.
 */
test('invoke', function () {
    $this->createIndex('testing-deletes-index');
    app()->make(DeletesIndex::class)('testing-deletes-index');
})->throwsNoExceptions();
