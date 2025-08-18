<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\BrokenMovie;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * Test SynchronizesModel::__invoke() method with invalid model.
 */
test('with invalid model', function () {
    app()->make(SynchronizesModel::class)(Movie::class);
})->throws(BadMethodCallException::class, 'Call to undefined method ' . Movie::class . '::meiliSettings()');

/**
 * Test SynchronizesModel::__invoke() method with invalid settings.
 */
test('with invalid settings', function () {
    app()->make(SynchronizesModel::class)(BrokenMovie::class);
    $this->deleteIndex(app(BrokenMovie::class)->searchableAs());
})->throws(ValidationException::class, 'The distinct attribute field must be a string.');

/**
 * Test SynchronizesModel::__invoke() method with advanced settings.
 */
test('with advanced settings', function () {
    try {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = app(MeiliMovie::class)->meiliSettings();
        $expected = collect($settings)
            ->mapWithKeys(function ($value, $key) use ($defaults) {
                $old = $defaults[$key];
                $new = $value;

                return [$key => $old === $new ? false : compact('old', 'new')];
            })
            ->filter()
            ->all()
        ;

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);

        $changes = app()->make(SynchronizesModel::class)(MeiliMovie::class);
        expect($changes)->toBe($expected);

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect(Arr::except($details, ['faceting', 'pagination', 'typoTolerance']))->toMatchArray($settings);
    } finally {
        $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test SynchronizesModel::__invoke() method with pretend option.
 */
test('with pretend', function () {
    try {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = app(MeiliMovie::class)->meiliSettings();
        $expected = collect($settings)
            ->mapWithKeys(function ($value, $key) use ($defaults) {
                $old = $defaults[$key];
                $new = $value;

                return [$key => $old === $new ? false : compact('old', 'new')];
            })
            ->filter()
            ->all()
        ;

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);

        $changes = app()->make(SynchronizesModel::class)(MeiliMovie::class, true);
        expect($changes)->toBe($expected);

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);
    } finally {
        $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test SynchronizesModel::__invoke() method with soft deletes enabled.
 */
test('with soft deletes enabled', function () {
    config(['scout.soft_delete' => true]);

    try {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = app(MeiliMovie::class)->meiliSettings();
        // Prepend '__soft_deleted' to filterable attributes.
        array_unshift($settings['filterableAttributes'], '__soft_deleted');
        $expected = collect($settings)
            ->mapWithKeys(function ($value, $key) use ($defaults) {
                $old = $defaults[$key];
                $new = $value;

                return [$key => $old === $new ? false : compact('old', 'new')];
            })
            ->filter()
            ->all()
        ;

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);

        $changes = app()->make(SynchronizesModel::class)(MeiliMovie::class);
        expect($changes)->toBe($expected);

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect(Arr::except($details, ['faceting', 'pagination', 'typoTolerance']))->toMatchArray($settings);
    } finally {
        $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
    }
});
