<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\TestCase;

uses(Dwarf\MeiliTools\Tests\TestCase::class);

/**
 * @internal
 */

/**
 * Test `meili:model:reset` command with advanced settings.
 */
test('with advanced settings', function () {
    try {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = app(MeiliMovie::class)->meiliSettings();

        app()->make(SynchronizesModel::class)(MeiliMovie::class);
        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        $this->assertNotSame($defaults, $details);
        $this->assertSame(array_replace($defaults, $settings), $details);

        $changes = collect($settings)
            ->mapWithKeys(function ($value, $key) {
                $old = $value;
                $new = null;

                return [$key => $old === $new ? false : compact('old', 'new')];
            })
            ->filter()
            ->all()
        ;
        $values = Helpers::convertIndexChangesToTable($changes);

        $this->artisan('meili:model:reset', ['model' => MeiliMovie::class])
            ->expectsTable(['Setting', 'Old', 'New'], $values)
            ->assertSuccessful()
        ;

        $this->artisan('meili:model:reset')
            ->expectsQuestion('What is the model class?', MeiliMovie::class)
            ->expectsTable(['Setting', 'Old', 'New'], [])
            ->assertSuccessful()
        ;

        $this->artisan('meili:model:reset')
            ->expectsQuestion('What is the model class?', 'MeiliMovie')
            ->expectsTable(['Setting', 'Old', 'New'], [])
            ->assertSuccessful()
        ;

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        $this->assertSame($defaults, $details);
    } finally {
        $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test `meili:model:reset` command with pretend option.
 */
test('with pretend', function () {
    try {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = app(MeiliMovie::class)->meiliSettings();

        app()->make(SynchronizesModel::class)(MeiliMovie::class);
        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        $this->assertNotSame($defaults, $details);
        $this->assertSame(array_replace($defaults, $settings), $details);

        $changes = collect($settings)
            ->mapWithKeys(function ($value, $key) {
                $old = $value;
                $new = null;

                return [$key => $old === $new ? false : compact('old', 'new')];
            })
            ->filter()
            ->all()
        ;
        $values = Helpers::convertIndexChangesToTable($changes);

        $this->artisan('meili:model:reset', ['model' => MeiliMovie::class, '--pretend' => true])
            ->expectsTable(['Setting', 'Old', 'New'], $values)
            ->assertSuccessful()
        ;

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        $this->assertNotSame($defaults, $details);
    } finally {
        $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
    }
});
