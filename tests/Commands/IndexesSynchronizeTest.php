<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
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
            ->assertFailed()
        ;

        if ($pretend) {
            expect(resolve(ListsIndexes::class)())->not->toHaveKey('testing-books');
        } else {
            expect(resolve(DetailsIndex::class)('testing-books'))->toMatchArray(array_replace($defaults, $settings));
        }
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

    // Laravel only asks for confirmation interactively outside of the testing environment.
    $this->artisan('meili:indexes:synchronize', ['--no-interaction' => true])
        ->expectsOutputToContain('Command cancelled.')
        ->assertFailed()
    ;

    $this->artisan('meili:indexes:synchronize', ['--force' => true])
        ->assertSuccessful()
    ;
});

/**
 * Test `meili:indexes:synchronize` command with an index belonging to a model.
 */
test('with model index', function (): void {
    config(['scout.meilisearch.index-settings' => [
        'books'        => ['sortableAttributes' => ['title']],
        'meili_movies' => ['sortableAttributes' => ['rating']],
    ]]);

    try {
        $this->artisan('meili:indexes:synchronize')
            ->expectsOutput('Skipped testing-meili_movies, synchronized by model ' . MeiliMovie::class)
            ->expectsOutput('Processed testing-books')
            ->doesntExpectOutput('Processed testing-meili_movies')
            ->assertSuccessful()
        ;
    } finally {
        $this->deleteIndex('testing-books');
    }
});

/**
 * Test `meili:indexes:synchronize` command with check option.
 */
test('with check', function (): void {
    App::detectEnvironment(fn (): string => 'production');
    config(['scout.meilisearch.index-settings' => [
        'authors' => ['sortableAttributes' => ['name']],
        'books'   => ['sortableAttributes' => ['title']],
    ]]);

    try {
        // Checking doesn't change anything, so it runs without confirmation in production.
        $this->artisan('meili:indexes:synchronize', ['--check' => true, '--no-interaction' => true])
            ->expectsOutput('Settings are out of sync for 2 indexes')
            ->assertFailed()
        ;

        expect(resolve(ListsIndexes::class)())->not->toHaveKey('testing-books');

        $this->artisan('meili:indexes:synchronize', ['--force' => true])->assertSuccessful();

        $this->artisan('meili:indexes:synchronize', ['--check' => true, '--no-interaction' => true])
            ->doesntExpectOutputToContain('Settings are out of sync')
            ->assertSuccessful()
        ;
    } finally {
        $this->deleteIndex('testing-authors');
        $this->deleteIndex('testing-books');
    }
});
