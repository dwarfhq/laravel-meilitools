<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesScoutIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Tools;
use Illuminate\Validation\ValidationException;

/**
 * Test SynchronizesScoutIndex::__invoke() method with an index which isn't configured.
 */
test('without configuration', function (): void {
    resolve(SynchronizesScoutIndex::class)('books');
})->throws(
    MeiliToolsException::class,
    "No settings configured for index 'testing-books' in 'scout.meilisearch.index-settings'",
);

/**
 * Test SynchronizesScoutIndex::__invoke() method with invalid settings.
 */
test('with invalid settings', function (): void {
    config(['scout.meilisearch.index-settings' => ['books' => ['distinctAttribute' => 42]]]);

    try {
        resolve(SynchronizesScoutIndex::class)('books');
    } finally {
        $this->deleteIndex('testing-books');
    }
})->throws(ValidationException::class, 'The distinct attribute field must be a string.');

/**
 * Test SynchronizesScoutIndex::__invoke() method with configured settings.
 */
test('with configured settings', function (bool $pretend): void {
    $settings = Tools::movieSettings();
    config(['scout.meilisearch.index-settings' => ['books' => $settings]]);
    $defaults = Helpers::defaultSettings();

    try {
        $changes = resolve(SynchronizesScoutIndex::class)('books', $pretend);
        expect($changes)->toHaveSameSize($settings);
        foreach ($changes as $key => $value) {
            expect($value)->toBe(['old' => $defaults[$key], 'new' => $settings[$key]]);
        }

        $details = resolve(DetailsIndex::class)('testing-books');
        expect($details)->toMatchArray($pretend ? $defaults : array_replace($defaults, $settings));

        // Prefixed index names work as well.
        $changes = resolve(SynchronizesScoutIndex::class)('testing-books', $pretend);
        expect($changes)->toHaveCount($pretend ? count($settings) : 0);
    } finally {
        $this->deleteIndex('testing-books');
    }
})->with(['synchronize' => false, 'pretend' => true]);
