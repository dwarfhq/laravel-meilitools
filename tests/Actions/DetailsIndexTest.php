<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use MeiliSearch\Exceptions\ApiException;
use MeiliSearch\Exceptions\CommunicationException;

/**
 * Test using wrong Scout driver.
 */
test('meili tools exception', function () {
    config(['scout.driver' => null]);

    app()->make(DetailsIndex::class)('testing-details-index');
})->throws(MeiliToolsException::class);

/**
 * Test getting index details when MeiliSearch isn't running.
 */
test('communication exception', function () {
    config(['scout.meilisearch.host' => 'http://localhost:7777']);

    app()->make(DetailsIndex::class)('testing-details-index');
})->throws(CommunicationException::class, 'Failed to connect to localhost port 7777');

/**
 * Test getting index details when it doesn't exist.
 */
test('api exception', function () {
    app()->make(DetailsIndex::class)('testing-details-index');
})->throws(ApiException::class, 'Index `testing-details-index` not found.');

/**
 * Test DetailsIndex::__invoke() method.
 */
test('invoke', function () {
    $this->withIndex('testing-details-index', function () {
        $details = app()->make(DetailsIndex::class)('testing-details-index');
        expect($details)->toMatchArray(Helpers::defaultSettings(Helpers::engineVersion()));
    });
});
