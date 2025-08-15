<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\BrokenMovie;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;

/**
 * Test `meili:models:synchronize` command.
 */
test('with advanced settings', function () {
    try {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = app(MeiliMovie::class)->meiliSettings();
        $changes = collect($settings)
            ->mapWithKeys(function ($value, $key) use ($defaults) {
                $old = $defaults[$key];
                $new = $value;

                return [$key => $old === $new ? false : compact('old', 'new')];
            })
            ->filter()
            ->all()
        ;
        $values = Helpers::convertIndexChangesToTable($changes);

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toBe($defaults);

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

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect(Arr::except($details, ['faceting', 'pagination', 'typoTolerance']))->toBe($settings);
    } finally {
        $this->deleteIndex(app(BrokenMovie::class)->searchableAs());
        $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test `meili:models:synchronize` command with pretend option.
 */
test('with pretend', function () {
    try {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = app(MeiliMovie::class)->meiliSettings();
        $changes = collect($settings)
            ->mapWithKeys(function ($value, $key) use ($defaults) {
                $old = $defaults[$key];
                $new = $value;

                return [$key => $old === $new ? false : compact('old', 'new')];
            })
            ->filter()
            ->all()
        ;
        $values = Helpers::convertIndexChangesToTable($changes);

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toBe($defaults);

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

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toBe($defaults);
    } finally {
        $this->deleteIndex(app(BrokenMovie::class)->searchableAs());
        $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test `meili:models:synchronize` command in production mode.
 */
test('in production mode', function () {
    App::detectEnvironment(fn () => 'production');

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
        $this->deleteIndex(app(BrokenMovie::class)->searchableAs());
        $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
    }
});
