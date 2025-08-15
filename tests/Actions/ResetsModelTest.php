<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\ResetsModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\TestCase;

uses(Dwarf\MeiliTools\Tests\TestCase::class);

/**
 * @internal
 */

/**
 * Test ResetsModel::__invoke() method with advanced settings.
 */
test('with advanced settings', function () {
    try {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = app(MeiliMovie::class)->meiliSettings();

        app()->make(SynchronizesModel::class)(MeiliMovie::class);
        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        $this->assertNotSame($defaults, $details);
        $this->assertSame(array_replace($defaults, $settings), $details);

        $changes = app()->make(ResetsModel::class)(MeiliMovie::class);
        $this->assertCount(8, $changes);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = null;
            $this->assertSame(compact('old', 'new'), $value);
        }

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        $this->assertSame($defaults, $details);
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
        $this->assertNotSame($defaults, $details);
        $this->assertSame(array_replace($defaults, $settings), $details);

        $changes = app()->make(ResetsModel::class)(MeiliMovie::class, true);
        $this->assertCount(8, $changes);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = null;
            $this->assertSame(compact('old', 'new'), $value);
        }

        $details = app()->make(DetailsModel::class)(MeiliMovie::class);
        $this->assertNotSame($defaults, $details);
    } finally {
        $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
    }
});
