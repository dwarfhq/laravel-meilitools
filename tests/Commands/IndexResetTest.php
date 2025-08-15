<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\TestCase;
use Dwarf\MeiliTools\Tests\Tools;

uses(Dwarf\MeiliTools\Tests\TestCase::class);

/**
 * @internal
 */

/**
 * Test `meili:index:reset` command with advanced settings.
 */
test('with advanced settings', function () {
    $this->withIndex(self::INDEX, function () {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = Tools::movieSettings();

        app()->make(SynchronizesIndex::class)(self::INDEX, $settings);
        $details = app()->make(DetailsIndex::class)(self::INDEX);
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

        $this->artisan('meili:index:reset', ['index' => self::INDEX])
            ->expectsTable(['Setting', 'Old', 'New'], $values)
            ->assertSuccessful()
        ;

        $this->artisan('meili:index:reset')
            ->expectsQuestion('What is the index name?', self::INDEX)
            ->expectsTable(['Setting', 'Old', 'New'], [])
            ->assertSuccessful()
        ;

        $details = app()->make(DetailsIndex::class)(self::INDEX);
        $this->assertSame($defaults, $details);
    });
});

/**
 * Test `meili:index:reset` command with pretend option.
 */
test('with pretend', function () {
    $this->withIndex(self::INDEX, function () {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = Tools::movieSettings();

        app()->make(SynchronizesIndex::class)(self::INDEX, $settings);
        $details = app()->make(DetailsIndex::class)(self::INDEX);
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

        $this->artisan('meili:index:reset', ['index' => self::INDEX, '--pretend' => true])
            ->expectsTable(['Setting', 'Old', 'New'], $values)
            ->assertSuccessful()
        ;

        $details = app()->make(DetailsIndex::class)(self::INDEX);
        $this->assertNotSame($defaults, $details);
    });
});
