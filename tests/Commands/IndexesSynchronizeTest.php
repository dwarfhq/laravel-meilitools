<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Dwarf\MeiliTools\Tests\Tools;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;

/**
 * Test `meili:indexes:synchronize` command.
 */
test('with configured settings', function (bool $pretend): void {
    $settings = Tools::movieSettings();
    config(['scout.meilisearch.index-settings' => [
        'authors'    => ['distinctAttribute' => 42],
        'books'      => $settings,
        Movie::class => ['sortableAttributes' => ['rating']],
    ]]);
    $defaults = Helpers::defaultSettings();

    $changes = collect($settings)->map(fn ($value, $key): array => ['old' => $defaults[$key], 'new' => $value])->all();
    $values = Helpers::convertIndexChangesToTable($changes);

    try {
        $this->artisan('meili:indexes:synchronize', ['--pretend' => $pretend])
            ->expectsOutput('Processed testing-authors')
            ->expectsOutput(sprintf(
                "Exception '%s' with message '%s'",
                ValidationException::class,
                'The distinct attribute field must be a string.',
            ))
            ->expectsOutput('Processed testing-books')
            ->expectsTable(['Setting', 'Old', 'New'], $values)
            ->doesntExpectOutput('Processed ' . Movie::class)
            ->assertSuccessful()
        ;

        $details = resolve(DetailsIndex::class)('testing-books');
        expect($details)->toMatchArray($pretend ? $defaults : array_replace($defaults, $settings));
    } finally {
        $this->deleteIndex('testing-authors');
        $this->deleteIndex('testing-books');
    }
})->with(['synchronize' => false, 'pretend' => true]);

/**
 * Test `meili:indexes:synchronize` command in production mode.
 */
test('in production mode', function (): void {
    App::detectEnvironment(fn (): string => 'production');

    $this->artisan('meili:indexes:synchronize')
        ->expectsConfirmation('Are you sure you want to run this command?', 'no')
        ->assertFailed()
    ;

    $this->artisan('meili:indexes:synchronize', ['--force' => true])
        ->assertSuccessful()
    ;
});
