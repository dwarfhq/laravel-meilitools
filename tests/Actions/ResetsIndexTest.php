<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\ResetsIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\TestCase;
use Dwarf\MeiliTools\Tests\Tools;


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
        expect($details)->toBe(array_replace($defaults, $settings));

        $changes = app()->make(ResetsIndex::class)(self::INDEX);
        expect($changes)->toHaveCount(8);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = null;
            expect($value)->toBe(compact('old', 'new'));
        }

        $details = app()->make(DetailsIndex::class)(self::INDEX);
        expect($details)->toBe($defaults);
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
        expect($details)->toBe(array_replace($defaults, $settings));

        $changes = app()->make(ResetsIndex::class)(self::INDEX, true);
        expect($changes)->toHaveCount(8);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = null;
            expect($value)->toBe(compact('old', 'new'));
        }

        $details = app()->make(DetailsIndex::class)(self::INDEX);
        $this->assertNotSame($defaults, $details);
    });
});
