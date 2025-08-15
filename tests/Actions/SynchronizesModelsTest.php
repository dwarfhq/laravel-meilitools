<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModels;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Illuminate\Support\Arr;

/**
 * Test SynchronizesModels::__invoke() method with advanced settings.
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
        $exception = new BadMethodCallException('Call to undefined method ' . Movie::class . '::meiliSettings()');

        $classes = [
            Movie::class      => $exception,
            MeiliMovie::class => $expected,
        ];

        $details = app()->make(DetailsModel::class)(Movie::class);
        expect($details)->toBe($defaults);
        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toBe($defaults);

        $action = app()->make(SynchronizesModels::class);
        $action(array_keys($classes), function ($class, $result) use ($classes) {
            if (\is_array($result)) {
                expect($result)->toBe($classes[$class]);
            } else {
                expect($classes[$class] instanceof $result)->toBeTrue();
                expect($result->getMessage())->toBe($classes[$class]->getMessage());
            }
        });

        $details = app()->make(DetailsModel::class)(Movie::class);
        expect($details)->toBe($defaults);

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect(Arr::except($details, ['faceting', 'pagination', 'typoTolerance']))->toBe($settings);
    } finally {
        $this->deleteIndex(app(Movie::class)->searchableAs());
        $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test SynchronizesModels::__invoke() method with pretend option.
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

        $action = app()->make(SynchronizesModels::class);
        $action([MeiliMovie::class], function ($class, $result) use ($expected) {
            expect($result)->toBe($expected);
        }, true);

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toBe($defaults);
    } finally {
        $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
    }
});
