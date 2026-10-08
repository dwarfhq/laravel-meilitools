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
test('with advanced settings', function (): void {
    try {
        $defaults = Helpers::defaultSettings();
        $settings = resolve(MeiliMovie::class)->meiliSettings();

        resolve(SynchronizesModel::class)(MeiliMovie::class);
        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->not->toBe($defaults)
            ->toMatchArray(array_replace($defaults, $settings))
        ;

        $changes = resolve(ResetsModel::class)(MeiliMovie::class);
        expect($changes)->toHaveCount(8);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = null;
            expect($value)->toBe(['old' => $old, 'new' => $new]);
        }

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);
    } finally {
        $this->deleteIndex(resolve(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test ResetsModel::__invoke() method with pretend option.
 */
test('with pretend', function (): void {
    try {
        $defaults = Helpers::defaultSettings();
        $settings = resolve(MeiliMovie::class)->meiliSettings();

        resolve(SynchronizesModel::class)(MeiliMovie::class);
        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->not->toBe($defaults)
            ->toMatchArray(array_replace($defaults, $settings))
        ;

        $changes = resolve(ResetsModel::class)(MeiliMovie::class, true);
        expect($changes)->toHaveCount(8);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = null;
            expect($value)->toBe(['old' => $old, 'new' => $new]);
        }

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->not->toBe($defaults);
    } finally {
        $this->deleteIndex(resolve(MeiliMovie::class)->searchableAs());
    }
});
