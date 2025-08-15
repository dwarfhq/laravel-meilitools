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
 * @internal
 */

/**
 * Test SynchronizesModel::__invoke() method with invalid model.
 */
test('with invalid model', function () {
    $this->expectException(BadMethodCallException::class);
    $this->expectExceptionMessage('Call to undefined method ' . Movie::class . '::meiliSettings()');

    app()->make(SynchronizesModel::class)(Movie::class);
});

/**
 * Test SynchronizesModel::__invoke() method with invalid settings.
 */
test('with invalid settings', function () {
    $version = app()->version();
    $message = 'The distinct attribute field must be a string.';
    if (version_compare($version, '10.0.0', '<')) {
        $message = 'The distinct attribute must be a string.';
    }
    if (version_compare($version, '9.0.0', '<')) {
        $message = 'The given data was invalid.';
    }
    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage($message);

    app()->make(SynchronizesModel::class)(BrokenMovie::class);
    $this->deleteIndex(app(BrokenMovie::class)->searchableAs());
});

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
        expect($details)->toBe($defaults);

        $changes = app()->make(SynchronizesModel::class)(MeiliMovie::class);
        expect($changes)->toBe($expected);

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect(Arr::except($details, ['faceting', 'pagination', 'typoTolerance']))->toBe($settings);
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
        expect($details)->toBe($defaults);

        $changes = app()->make(SynchronizesModel::class)(MeiliMovie::class, true);
        expect($changes)->toBe($expected);

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toBe($defaults);
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
        expect($details)->toBe($defaults);

        $changes = app()->make(SynchronizesModel::class)(MeiliMovie::class);
        expect($changes)->toBe($expected);

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect(Arr::except($details, ['faceting', 'pagination', 'typoTolerance']))->toBe($settings);
    } finally {
        $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
    }
});
