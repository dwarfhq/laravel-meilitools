<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Tools;

/**
 * Test `meili:index:synchronize` command.
 */
test('with configured settings', function (bool $pretend): void {
    $settings = Tools::movieSettings();
    config(['scout.meilisearch.index-settings' => ['books' => $settings]]);
    $defaults = Helpers::defaultSettings();

    $changes = collect($settings)->map(fn ($value, $key): array => ['old' => $defaults[$key], 'new' => $value])->all();
    $values = Helpers::convertIndexChangesToTable($changes);

    try {
        $this->artisan('meili:index:synchronize', ['index' => 'books', '--pretend' => $pretend])
            ->expectsTable(['Setting', 'Old', 'New'], $values)
            ->assertSuccessful()
        ;

        $details = resolve(DetailsIndex::class)('testing-books');
        expect($details)->toMatchArray($pretend ? $defaults : array_replace($defaults, $settings));

        $this->artisan('meili:index:synchronize')
            ->expectsQuestion('What is the index name?', 'books')
            ->assertSuccessful()
        ;
    } finally {
        $this->deleteIndex('testing-books');
    }
})->with(['synchronize' => false, 'pretend' => true]);

/**
 * Test `meili:index:synchronize` command without configuration.
 */
test('without configuration', function (): void {
    $this->artisan('meili:index:synchronize', ['index' => 'books']);
})->throws(
    MeiliToolsException::class,
    "No settings configured for index 'testing-books' in 'scout.meilisearch.index-settings'",
);

/**
 * Test `meili:index:synchronize` command with check option.
 */
test('with check', function (): void {
    config(['scout.meilisearch.index-settings' => ['books' => Tools::movieSettings()]]);

    try {
        $this->artisan('meili:index:synchronize', ['index' => 'books', '--check' => true])
            ->expectsOutput('Settings are out of sync')
            ->assertFailed()
        ;

        expect(resolve(DetailsIndex::class)('testing-books'))->toMatchArray(Helpers::defaultSettings());

        $this->artisan('meili:index:synchronize', ['index' => 'books'])->assertSuccessful();

        $this->artisan('meili:index:synchronize', ['index' => 'books', '--check' => true])
            ->doesntExpectOutput('Settings are out of sync')
            ->assertSuccessful()
        ;
    } finally {
        $this->deleteIndex('testing-books');
    }
});
