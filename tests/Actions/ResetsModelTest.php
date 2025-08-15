<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\ResetsModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;

/**
 * Test ResetsModel::__invoke() method with advanced settings.
 */
test('with advanced settings', function () {
    try {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = app(MeiliMovie::class)->meiliSettings();

        app()->make(SynchronizesModel::class)(MeiliMovie::class);
        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect($details)->not->toBe($defaults);
        expect($details)->toBe(array_replace($defaults, $settings));

        $changes = app()->make(ResetsModel::class)(MeiliMovie::class);
        expect($changes)->toHaveCount(8);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = null;
            expect($value)->toBe(compact('old', 'new'));
        }

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toBe($defaults);
    } finally {
        $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test ResetsModel::__invoke() method with pretend option.
 */
test('with pretend', function () {
    try {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = app(MeiliMovie::class)->meiliSettings();

        app()->make(SynchronizesModel::class)(MeiliMovie::class);
        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect($details)->not->toBe($defaults);
        expect($details)->toBe(array_replace($defaults, $settings));

        $changes = app()->make(ResetsModel::class)(MeiliMovie::class, true);
        expect($changes)->toHaveCount(8);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = null;
            expect($value)->toBe(compact('old', 'new'));
        }

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        expect($details)->not->toBe($defaults);
    } finally {
        $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
    }
});
