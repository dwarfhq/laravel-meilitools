<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\ResetsIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\TestCase;
use Dwarf\MeiliTools\Tests\Tools;

uses(Dwarf\MeiliTools\Tests\TestCase::class);

/**
 * @internal
 */

/**
 * Test ResetsIndex::__invoke() method with movie settings.
 */
test('with movie settings', function () {
    $this->withIndex(self::INDEX, function () {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = Tools::movieSettings();

        app()->make(SynchronizesIndex::class)(self::INDEX, $settings);
        $details = app()->make(DetailsIndex::class)(self::INDEX);
        $this->assertNotSame($defaults, $details);
        $this->assertSame(array_replace($defaults, $settings), $details);

        $changes = app()->make(ResetsIndex::class)(self::INDEX);
        $this->assertCount(8, $changes);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = null;
            $this->assertSame(compact('old', 'new'), $value);
        }

        $details = app()->make(DetailsIndex::class)(self::INDEX);
        $this->assertSame($defaults, $details);
    });
});

/**
 * Test ResetsIndex::__invoke() method with pretend.
 */
test('with pretend', function () {
    $this->withIndex(self::INDEX, function () {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = Tools::movieSettings();

        app()->make(SynchronizesIndex::class)(self::INDEX, $settings);
        $details = app()->make(DetailsIndex::class)(self::INDEX);
        $this->assertNotSame($defaults, $details);
        $this->assertSame(array_replace($defaults, $settings), $details);

        $changes = app()->make(ResetsIndex::class)(self::INDEX, true);
        $this->assertCount(8, $changes);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = null;
            $this->assertSame(compact('old', 'new'), $value);
        }

        $details = app()->make(DetailsIndex::class)(self::INDEX);
        $this->assertNotSame($defaults, $details);
    });
});
