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
test('with movie settings', function (): void {
    $this->withIndex('testing-resets-index', function (): void {
        $defaults = Helpers::defaultSettings();
        $settings = Tools::movieSettings();

        resolve(SynchronizesIndex::class)('testing-resets-index', $settings);
        $details = resolve(DetailsIndex::class)('testing-resets-index');
        expect($details)->not->toBe($defaults)
            ->toMatchArray(array_replace($defaults, $settings))
        ;

        $changes = resolve(ResetsIndex::class)('testing-resets-index');
        expect($changes)->toHaveCount(8);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = null;
            expect($value)->toBe(['old' => $old, 'new' => $new]);
        }

        $details = resolve(DetailsIndex::class)('testing-resets-index');
        expect($details)->toMatchArray($defaults);
    });
});

/**
 * Test ResetsIndex::__invoke() method with pretend.
 */
test('with pretend', function (): void {
    $this->withIndex('testing-resets-index', function (): void {
        $defaults = Helpers::defaultSettings();
        $settings = Tools::movieSettings();

        resolve(SynchronizesIndex::class)('testing-resets-index', $settings);
        $details = resolve(DetailsIndex::class)('testing-resets-index');
        expect($details)->not->toBe($defaults)
            ->toMatchArray(array_replace($defaults, $settings))
        ;

        $changes = resolve(ResetsIndex::class)('testing-resets-index', true);
        expect($changes)->toHaveCount(8);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = null;
            expect($value)->toBe(['old' => $old, 'new' => $new]);
        }

        $details = resolve(DetailsIndex::class)('testing-resets-index');
        expect($details)->not->toBe($defaults);
    });
});
