<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\TestCase;
use MeiliSearch\Exceptions\ApiException;
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

    $action = app()->make(DetailsIndex::class);
    $details = ($action)(self::INDEX);
});

/**
 * Test getting index details when MeiliSearch isn't running.
 */
test('communication exception', function () {
    config(['scout.meilisearch.host' => 'http://localhost:7777']);

    $this->expectException(CommunicationException::class);
    $this->expectExceptionMessage('Failed to connect to localhost port 7777');

    $action = app()->make(DetailsIndex::class);
    $details = ($action)(self::INDEX);
});

/**
 * Test getting index details when it doesn't exist.
 */
test('api exception', function () {
    $this->expectException(ApiException::class);
    $this->expectExceptionMessage('Index `' . self::INDEX . '` not found.');

    $action = app()->make(DetailsIndex::class);
    $details = ($action)(self::INDEX);
});

/**
 * Test DetailsIndex::__invoke() method.
 */
test('invoke', function () {
    $this->withIndex(self::INDEX, function () {
        $action = app()->make(DetailsIndex::class);
        $details = ($action)(self::INDEX);
        expect($details)->toBe(Helpers::defaultSettings(Helpers::engineVersion()));
    });
});
