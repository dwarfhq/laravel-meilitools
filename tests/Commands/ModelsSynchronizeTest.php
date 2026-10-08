<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\BrokenMovie;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Dwarf\MeiliTools\Tests\Tools;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;

/**
 * Test `meili:models:synchronize` command.
 */
test('with advanced settings', function (): void {
    try {
        $defaults = Helpers::defaultSettings();
        $settings = resolve(MeiliMovie::class)->meiliSettings();
        $changes = Tools::changes($defaults, $settings);
        $values = Helpers::convertIndexChangesToTable($changes);

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);

        $this->artisan('meili:models:synchronize')
            ->expectsOutput('Processed ' . BrokenMovie::class)
            ->expectsOutput(sprintf(
                "Exception '%s' with message '%s'",
                ValidationException::class,
                'The distinct attribute field must be a string.'
            ))
            ->expectsOutput('Processed ' . MeiliMovie::class)
            ->expectsTable(['Setting', 'Old', 'New'], $values)
            ->assertSuccessful()
        ;

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect(Arr::except($details, ['faceting', 'pagination', 'typoTolerance']))->toMatchArray($settings);
    } finally {
        $this->deleteIndex(resolve(BrokenMovie::class)->searchableAs());
        $this->deleteIndex(resolve(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test `meili:models:synchronize` command with pretend option.
 */
test('with pretend', function (): void {
    try {
        $defaults = Helpers::defaultSettings();
        $settings = resolve(MeiliMovie::class)->meiliSettings();
        $changes = Tools::changes($defaults, $settings);
        $values = Helpers::convertIndexChangesToTable($changes);

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);

        $path = __DIR__ . '/../Models';
        $namespace = 'Dwarf\\MeiliTools\\Tests\\Models';
        config(['meilitools.paths' => [$path => $namespace]]);

        $this->artisan('meili:models:synchronize', ['--pretend' => true])
            ->expectsOutput('Processed ' . BrokenMovie::class)
            ->expectsOutput(sprintf(
                "Exception '%s' with message '%s'",
                ValidationException::class,
                'The distinct attribute field must be a string.'
            ))
            ->expectsOutput('Processed ' . MeiliMovie::class)
            ->expectsTable(['Setting', 'Old', 'New'], $values)
            ->assertSuccessful()
        ;

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);
    } finally {
        $this->deleteIndex(resolve(BrokenMovie::class)->searchableAs());
        $this->deleteIndex(resolve(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test `meili:models:synchronize` command in production mode.
 */
test('in production mode', function (): void {
    App::detectEnvironment(fn (): string => 'production');

    try {
        $this->artisan('meili:models:synchronize')
            ->expectsConfirmation('Are you sure you want to run this command?', 'no')
            ->assertFailed()
        ;

        $this->artisan('meili:models:synchronize', ['--force' => true])
            ->assertSuccessful()
        ;

        $this->artisan('meili:models:synchronize')
            ->expectsConfirmation('Are you sure you want to run this command?', 'yes')
            ->assertSuccessful()
        ;
    } finally {
        $this->deleteIndex(resolve(BrokenMovie::class)->searchableAs());
        $this->deleteIndex(resolve(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test `meili:models:synchronize` command with models configured in Scout.
 */
test('with scout settings', function (): void {
    config([
        'meilitools.paths'                 => [],
        'scout.meilisearch.index-settings' => [
            Movie::class => ['sortableAttributes' => ['rating']],
            'books'      => ['sortableAttributes' => ['title']],
        ],
    ]);

    try {
        $this->artisan('meili:models:synchronize')
            ->expectsOutput('Processed ' . Movie::class)
            ->expectsTable(['Setting', 'Old', 'New'], [['Sortable Attributes', '[]', "['rating']"]])
            ->doesntExpectOutput('Processed books')
            ->doesntExpectOutput('Processed ' . MeiliMovie::class)
            ->assertSuccessful()
        ;

        $details = resolve(DetailsModel::class)(Movie::class);
        expect($details['sortableAttributes'])->toBe(['rating']);
    } finally {
        $this->deleteIndex(resolve(Movie::class)->searchableAs());
    }
});
