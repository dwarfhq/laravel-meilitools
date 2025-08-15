<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\TestCase;
use Illuminate\Support\Arr;


/**
 * @internal
 */

/**
 * Test `meili:model:synchronize` command with advanced settings.
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

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toBe($defaults);

        $values = Helpers::convertIndexChangesToTable($changes);

        $this->artisan('meili:model:synchronize', ['model' => MeiliMovie::class])
            ->expectsTable(['Setting', 'Old', 'New'], $values)
            ->assertSuccessful()
        ;

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect(Arr::except($details, ['faceting', 'pagination', 'typoTolerance']))->toBe($settings);
    } finally {
        $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test `meili:model:synchronize` command with pretend option.
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

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toBe($defaults);

        $values = Helpers::convertIndexChangesToTable($changes);

        $this->artisan('meili:model:synchronize', ['model' => MeiliMovie::class, '--pretend' => true])
            ->expectsTable(['Setting', 'Old', 'New'], $values)
            ->assertSuccessful()
        ;

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toBe($defaults);
    } finally {
        $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
    }
});
