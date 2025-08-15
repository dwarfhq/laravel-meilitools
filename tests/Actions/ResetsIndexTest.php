<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\ResetsIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Tools;

/**
 * Test ResetsIndex::__invoke() method with movie settings.
 */
test('with movie settings', function () {
    $this->withIndex('testing-resets-index', function () {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = Tools::movieSettings();

        app()->make(SynchronizesIndex::class)('testing-resets-index', $settings);
        $details = app()->make(DetailsIndex::class)('testing-resets-index');
        expect($details)->not->toBe($defaults);
        expect($details)->toBe(array_replace($defaults, $settings));

        $changes = app()->make(ResetsIndex::class)('testing-resets-index');
        expect($changes)->toHaveCount(8);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = null;
            expect($value)->toBe(compact('old', 'new'));
        }

        $details = app()->make(DetailsIndex::class)('testing-resets-index');
        expect($details)->toBe($defaults);
    });
});

/**
 * Test ResetsIndex::__invoke() method with pretend.
 */
test('with pretend', function () {
    $this->withIndex('testing-resets-index', function () {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = Tools::movieSettings();

        app()->make(SynchronizesIndex::class)('testing-resets-index', $settings);
        $details = app()->make(DetailsIndex::class)('testing-resets-index');
        expect($details)->not->toBe($defaults);
        expect($details)->toBe(array_replace($defaults, $settings));

        $changes = app()->make(ResetsIndex::class)('testing-resets-index', true);
        expect($changes)->toHaveCount(8);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = null;
            expect($value)->toBe(compact('old', 'new'));
        }

        $details = app()->make(DetailsIndex::class)('testing-resets-index');
        expect($details)->not->toBe($defaults);
    });
});
