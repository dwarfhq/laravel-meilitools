<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Exceptions\CommunicationException;

/**
 * Test using wrong Scout driver.
 */
test('meili tools exception', function (): void {
    config(['scout.driver' => null]);

    resolve(DetailsIndex::class)('testing-details-index');
})->throws(MeiliToolsException::class);

/**
 * Test getting index details when MeiliSearch isn't running.
 */
test('communication exception', function (): void {
    config(['scout.meilisearch.host' => 'http://localhost:7777']);

    resolve(DetailsIndex::class)('testing-details-index');
})->throws(CommunicationException::class, 'Failed to connect to localhost');

/**
 * Test getting index details when it doesn't exist.
 */
test('api exception', function (): void {
    resolve(DetailsIndex::class)('testing-details-index');
})->throws(ApiException::class, 'Index `testing-details-index` not found.');

/**
 * Test DetailsIndex::__invoke() method.
 */
test('invoke', function (): void {
    $this->withIndex('testing-details-index', function (): void {
        $details = resolve(DetailsIndex::class)('testing-details-index');
        expect($details)->toMatchArray(Helpers::defaultSettings());
    });
});
