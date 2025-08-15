<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\TestCase;
use Dwarf\MeiliTools\Tests\Tools;
use Illuminate\Support\Arr;


/**
 * @internal
 */

/**
 * Test SynchronizesIndex::__invoke() method with movie settings.
 */
test('with changing movie settings', function () {
    $this->withIndex(self::INDEX, function () {
        $action = app()->make(SynchronizesIndex::class);

        $changes = ($action)(self::INDEX, []);
        expect($changes)->toBe([]);

        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = Tools::movieSettings();

        $changes = ($action)(self::INDEX, $settings);
        expect($changes)->toHaveCount(8);

        foreach ($changes as $key => $value) {
            $old = $defaults[$key];
            $new = $settings[$key];
            expect($value)->toBe(compact('old', 'new'));
        }

        $update1 = [
            'stopWords'          => null,
            'sortableAttributes' => null,
            'synonyms'           => null,
        ];
        $changes = ($action)(self::INDEX, $update1);
        expect($changes)->toHaveCount(3);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = $update1[$key];
            expect($value)->toBe(compact('old', 'new'));
        }

        $update2 = [
            'distinctAttribute'   => 'movie_id',
            'displayedAttributes' => null,
            'stopWords'           => null,
        ];
        $changes = ($action)(self::INDEX, $update2);
        expect($changes)->toHaveCount(1);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = $update2[$key];
            expect($value)->toBe(compact('old', 'new'));
        }

        $update3 = [
            'distinctAttribute'    => null,
            'searchableAttributes' => null,
            'displayedAttributes'  => null,
            'stopWords'            => $settings['stopWords'],
        ];
        $changes = ($action)(self::INDEX, $update3);
        expect($changes)->toHaveCount(3);

        foreach ($changes as $key => $value) {
            $old = $key === 'stopWords' ? $defaults[$key] : $settings[$key];
            $new = $update3[$key];
            expect($value)->toBe(compact('old', 'new'));
        }

        $update4 = [
            'displayedAttributes'  => null,
            'distinctAttribute'    => null,
            'filterableAttributes' => null,
            'rankingRules'         => null,
            'searchableAttributes' => null,
            'sortableAttributes'   => null,
            'stopWords'            => null,
            'synonyms'             => null,
        ];
        $changes = ($action)(self::INDEX, $update4);
        expect($changes)->toHaveCount(3);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = $update4[$key];
            expect($value)->toBe(compact('old', 'new'));
        }

        $changes = ($action)(self::INDEX, $update4);
        expect($changes)->toBeEmpty();
    });
});

/**
 * Test SynchronizesIndex::__invoke() method with typo tolerance settings.
 */
test('with typo tolerance settings', function () {
    // Check if test should be run on this engine version.
    $version = Helpers::engineVersion() ?: '0.0.0';
    if (version_compare($version, '0.27.0', '<')) {
        $this->markTestSkipped('Typo tolerance is only available from 0.27.0 and up.');
    }

    $this->withIndex(self::INDEX, function () use ($version) {
        $action = app()->make(SynchronizesIndex::class);

        // Grab default settings.
        $defaults = Helpers::defaultSettings($version);
        $default = $defaults['typoTolerance'];

        $update = ['enabled' => false];
        $current = Arr::only($default, array_keys($update));
        $updates = ['typoTolerance' => $update];
        $expected = ['typoTolerance' => ['old' => $current, 'new' => $update]];
        $changes = ($action)(self::INDEX, $updates);
        expect($changes)->toHaveCount(1);
        expect($changes['typoTolerance']['old'])->toHaveCount(1);
        expect($changes['typoTolerance']['new'])->toHaveCount(1);
        expect($changes)->toBe($expected);

        // Attempting to do the same updates should result in zero changes.
        $changes = ($action)(self::INDEX, $updates);
        expect($changes)->toHaveCount(0);

        $default = array_replace($default, $update);
        $update = [
            'enabled'             => null,
            'minWordSizeForTypos' => [
                'oneTypo'  => 2,
                'twoTypos' => 6,
            ],
        ];
        $current = Arr::only($default, array_keys($update));
        $updates = ['typoTolerance' => $update];
        $expected = ['typoTolerance' => ['old' => $current, 'new' => $update]];
        $changes = ($action)(self::INDEX, $updates);
        expect($changes)->toHaveCount(1);
        expect($changes['typoTolerance']['old'])->toHaveCount(2);
        expect($changes['typoTolerance']['new'])->toHaveCount(2);
        expect($changes)->toBe($expected);

        // Attempting to do the same updates should result in zero changes.
        $changes = ($action)(self::INDEX, $updates);
        expect($changes)->toHaveCount(0);

        $default = array_replace($default, $update, Arr::only($defaults['typoTolerance'], 'enabled'));
        $update = [
            'minWordSizeForTypos' => null,
            'disableOnWords'      => ['title', 'rank'],
        ];
        $current = Arr::only($default, array_keys($update));
        $updates = ['typoTolerance' => $update];
        sort($update['disableOnWords']); // List is sorted automatically before update.
        $expected = ['typoTolerance' => ['old' => $current, 'new' => $update]];
        $changes = ($action)(self::INDEX, $updates);
        expect($changes)->toHaveCount(1);
        expect($changes['typoTolerance']['old'])->toHaveCount(2);
        expect($changes['typoTolerance']['new'])->toHaveCount(2);
        expect($changes)->toBe($expected);

        // Attempting to do the same updates should result in zero changes.
        $changes = ($action)(self::INDEX, $updates);
        expect($changes)->toHaveCount(0);

        $default = array_replace($default, $update, Arr::only($defaults['typoTolerance'], 'minWordSizeForTypos'));
        $update = [
            'disableOnWords'      => null,
            'disableOnAttributes' => ['title', 'rank'],
        ];
        $current = Arr::only($default, array_keys($update));
        $updates = ['typoTolerance' => $update];
        sort($update['disableOnAttributes']); // List is sorted automatically before update.
        $expected = ['typoTolerance' => ['old' => $current, 'new' => $update]];
        $changes = ($action)(self::INDEX, $updates);
        expect($changes)->toHaveCount(1);
        expect($changes['typoTolerance']['old'])->toHaveCount(2);
        expect($changes['typoTolerance']['new'])->toHaveCount(2);
        expect($changes)->toBe($expected);

        // Attempting to do the same updates should result in zero changes.
        $changes = ($action)(self::INDEX, $updates);
        expect($changes)->toHaveCount(0);

        $default = Arr::only($update, 'disableOnAttributes');
        $update = [
            'minWordSizeForTypos' => null,
            'disableOnWords'      => null,
            'disableOnAttributes' => null,
        ];
        $current = Arr::only($default, array_keys($update));
        $updates = ['typoTolerance' => $update];
        $expected = ['typoTolerance' => ['old' => $current, 'new' => Arr::only($update, 'disableOnAttributes')]];
        $changes = ($action)(self::INDEX, $updates);
        expect($changes)->toHaveCount(1);
        expect($changes['typoTolerance']['old'])->toHaveCount(1);
        expect($changes['typoTolerance']['new'])->toHaveCount(1);
        expect($changes)->toBe($expected);

        // Attempting to do the same updates should result in zero changes.
        $changes = ($action)(self::INDEX, $updates);
        expect($changes)->toHaveCount(0);
    });
});

/**
 * Test SynchronizesIndex::__invoke() method with pretend.
 */
test('with pretend', function () {
    $this->withIndex(self::INDEX, function () {
        $action = app()->make(SynchronizesIndex::class);

        $changes = ($action)(self::INDEX, []);
        expect($changes)->toBe([]);

        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = Tools::movieSettings();

        $changes = ($action)(self::INDEX, $settings, true);
        expect($changes)->toHaveCount(8);

        foreach ($changes as $key => $value) {
            $old = $defaults[$key];
            $new = $settings[$key];
            expect($value)->toBe(compact('old', 'new'));
        }

        $update = [
            'displayedAttributes'  => null,
            'distinctAttribute'    => null,
            'filterableAttributes' => null,
            'rankingRules'         => null,
            'searchableAttributes' => null,
            'sortableAttributes'   => null,
            'stopWords'            => null,
            'synonyms'             => null,
        ];
        $changes = ($action)(self::INDEX, $update);
        expect($changes)->toBeEmpty();
    });
});
