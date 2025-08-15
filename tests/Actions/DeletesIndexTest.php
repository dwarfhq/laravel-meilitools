<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DeletesIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Tests\TestCase;
use MeiliSearch\Exceptions\CommunicationException;

uses(Dwarf\MeiliTools\Tests\TestCase::class);

/**
 * @internal
 */

/**
 * Test using wrong Scout driver.
 */
test('meili tools exception', function () {
    config(['scout.driver' => null]);

    $this->expectException(MeiliToolsException::class);

    $action = app()->make(DeletesIndex::class);
    $delete = ($action)(self::INDEX);
});

/**
 * Test deleting index when MeiliSearch isn't running.
 */
test('communication exception', function () {
    config(['scout.meilisearch.host' => 'http://localhost:7777']);

    $this->expectException(CommunicationException::class);
    $this->expectExceptionMessage('Failed to connect to localhost port 7777');

    $action = app()->make(DeletesIndex::class);
    ($action)(self::INDEX);
});

/**
 * Test deleting index when it doesn't exist.
 */
test('index missing', function () {
    // No errors will be thrown in this case.
    $action = app()->make(DeletesIndex::class);
    ($action)(self::INDEX);
});

/**
 * Test DeletesIndex::__invoke() method.
 */
test('invoke', function () {
    $this->createIndex(self::INDEX);
    $action = app()->make(DeletesIndex::class);
    ($action)(self::INDEX);
});
