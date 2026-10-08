<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Tools;
use Illuminate\Support\Arr;

/**
 * Test SynchronizesIndex::__invoke() method with movie settings.
 */
test('with changing movie settings', function (): void {
    $this->withIndex('testing-synchronizes-index', function (): void {
        $action = resolve(SynchronizesIndex::class);

        $changes = $action('testing-synchronizes-index', []);
        expect($changes)->toBeEmpty();

        $defaults = Helpers::defaultSettings();
        $settings = Tools::movieSettings();

        $changes = $action('testing-synchronizes-index', $settings);
        expect($changes)->toHaveCount(8);

        foreach ($changes as $key => $value) {
            $old = $defaults[$key];
            $new = $settings[$key];
            expect($value)->toBe(['old' => $old, 'new' => $new]);
        }

        $update1 = [
            'stopWords'          => null,
            'sortableAttributes' => null,
            'synonyms'           => null,
        ];
        $changes = $action('testing-synchronizes-index', $update1);
        expect($changes)->toHaveCount(3);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = $update1[$key];
            expect($value)->toBe(['old' => $old, 'new' => $new]);
        }

        $update2 = [
            'distinctAttribute'   => 'movie_id',
            'displayedAttributes' => null,
            'stopWords'           => null,
        ];
        $changes = $action('testing-synchronizes-index', $update2);
        expect($changes)->toHaveCount(1);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = $update2[$key];
            expect($value)->toBe(['old' => $old, 'new' => $new]);
        }

        $update3 = [
            'distinctAttribute'    => null,
            'searchableAttributes' => null,
            'displayedAttributes'  => null,
            'stopWords'            => $settings['stopWords'],
        ];
        $changes = $action('testing-synchronizes-index', $update3);
        expect($changes)->toHaveCount(3);

        foreach ($changes as $key => $value) {
            $old = $key === 'stopWords' ? $defaults[$key] : $settings[$key];
            $new = $update3[$key];
            expect($value)->toBe(['old' => $old, 'new' => $new]);
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
        $changes = $action('testing-synchronizes-index', $update4);
        expect($changes)->toHaveCount(3);

        foreach ($changes as $key => $value) {
            $old = $settings[$key];
            $new = $update4[$key];
            expect($value)->toBe(['old' => $old, 'new' => $new]);
        }

        $changes = $action('testing-synchronizes-index', $update4);
        expect($changes)->toBeEmpty();
    });
});

/**
 * Test SynchronizesIndex::__invoke() method with typo tolerance settings.
 */
test('with typo tolerance settings', function (): void {
    $this->withIndex('testing-synchronizes-index', function (): void {
        $action = resolve(SynchronizesIndex::class);

        $defaults = Helpers::defaultSettings();
        $default = $defaults['typoTolerance'];

        $update = ['enabled' => false];
        $current = Arr::only($default, array_keys($update));
        $updates = ['typoTolerance' => $update];
        $expected = ['typoTolerance' => ['old' => $current, 'new' => $update]];
        $changes = $action('testing-synchronizes-index', $updates);
        expect($changes)->toHaveCount(1)
            ->and($changes['typoTolerance']['old'])->toHaveCount(1)
            ->and($changes['typoTolerance']['new'])->toHaveCount(1)
            ->and($changes)->toBe($expected)
        ;

        $changes = $action('testing-synchronizes-index', $updates);
        expect($changes)->toBeEmpty();

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
        $changes = $action('testing-synchronizes-index', $updates);
        expect($changes)->toHaveCount(1)
            ->and($changes['typoTolerance']['old'])->toHaveCount(2)
            ->and($changes['typoTolerance']['new'])->toHaveCount(2)
            ->and($changes)->toBe($expected)
        ;

        $changes = $action('testing-synchronizes-index', $updates);
        expect($changes)->toBeEmpty();

        $default = array_replace($default, $update, Arr::only($defaults['typoTolerance'], 'enabled'));
        $update = [
            'minWordSizeForTypos' => null,
            'disableOnWords'      => ['title', 'rank'],
        ];
        $current = Arr::only($default, array_keys($update));
        $updates = ['typoTolerance' => $update];
        sort($update['disableOnWords']); // List is sorted automatically before update.
        $expected = ['typoTolerance' => ['old' => $current, 'new' => $update]];
        $changes = $action('testing-synchronizes-index', $updates);
        expect($changes)->toHaveCount(1)
            ->and($changes['typoTolerance']['old'])->toHaveCount(2)
            ->and($changes['typoTolerance']['new'])->toHaveCount(2)
            ->and($changes)->toBe($expected)
        ;

        $changes = $action('testing-synchronizes-index', $updates);
        expect($changes)->toBeEmpty();

        $default = array_replace($default, $update, Arr::only($defaults['typoTolerance'], 'minWordSizeForTypos'));
        $update = [
            'disableOnWords'      => null,
            'disableOnAttributes' => ['title', 'rank'],
        ];
        $current = Arr::only($default, array_keys($update));
        $updates = ['typoTolerance' => $update];
        sort($update['disableOnAttributes']); // List is sorted automatically before update.
        $expected = ['typoTolerance' => ['old' => $current, 'new' => $update]];
        $changes = $action('testing-synchronizes-index', $updates);
        expect($changes)->toHaveCount(1)
            ->and($changes['typoTolerance']['old'])->toHaveCount(2)
            ->and($changes['typoTolerance']['new'])->toHaveCount(2)
            ->and($changes)->toBe($expected)
        ;

        $changes = $action('testing-synchronizes-index', $updates);
        expect($changes)->toBeEmpty();

        $default = Arr::only($update, 'disableOnAttributes');
        $update = [
            'minWordSizeForTypos' => null,
            'disableOnWords'      => null,
            'disableOnAttributes' => null,
        ];
        $current = Arr::only($default, array_keys($update));
        $updates = ['typoTolerance' => $update];
        $expected = ['typoTolerance' => ['old' => $current, 'new' => Arr::only($update, 'disableOnAttributes')]];
        $changes = $action('testing-synchronizes-index', $updates);
        expect($changes)->toHaveCount(1)
            ->and($changes['typoTolerance']['old'])->toHaveCount(1)
            ->and($changes['typoTolerance']['new'])->toHaveCount(1)
            ->and($changes)->toBe($expected)
        ;

        $changes = $action('testing-synchronizes-index', $updates);
        expect($changes)->toBeEmpty();
    });
});

/**
 * Test SynchronizesIndex::__invoke() method with pretend.
 */
test('with pretend', function (): void {
    $this->withIndex('testing-synchronizes-index', function (): void {
        $action = resolve(SynchronizesIndex::class);

        $changes = $action('testing-synchronizes-index', []);
        expect($changes)->toBeEmpty();

        $defaults = Helpers::defaultSettings();
        $settings = Tools::movieSettings();

        $changes = $action('testing-synchronizes-index', $settings, true);
        expect($changes)->toHaveCount(8);

        foreach ($changes as $key => $value) {
            $old = $defaults[$key];
            $new = $settings[$key];
            expect($value)->toBe(['old' => $old, 'new' => $new]);
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
        $changes = $action('testing-synchronizes-index', $update);
        expect($changes)->toBeEmpty();
    });
});

/**
 * Test SynchronizesIndex::__invoke() method with advanced settings.
 */
test('with advanced settings', function (): void {
    $this->withIndex('testing-synchronizes-index', function (): void {
        $action = resolve(SynchronizesIndex::class);

        $defaults = Helpers::defaultSettings();
        $settings = Tools::advancedSettings();

        $changes = $action('testing-synchronizes-index', $settings);
        expect($changes)->toHaveSameSize($settings);

        foreach ($changes as $key => $value) {
            $old = is_array($defaults[$key]) && is_array($settings[$key]) && Arr::isAssoc($settings[$key])
                ? Arr::only($defaults[$key], array_keys($settings[$key]))
                : $defaults[$key];
            expect($value)->toBe(['old' => $old, 'new' => $settings[$key]]);
        }

        expect($action('testing-synchronizes-index', $settings))->toBeEmpty()
            ->and($action('testing-synchronizes-index', Tools::advancedSettings(false)))->toBeEmpty()
        ;

        $changes = $action('testing-synchronizes-index', array_fill_keys(array_keys($settings), null));
        expect($changes)->toHaveSameSize($settings)
            ->and($action('testing-synchronizes-index', $settings, true))->toHaveSameSize($settings)
        ;
    });
});

/**
 * Test SynchronizesIndex::__invoke() method with partially merged settings.
 */
test('with partial settings', function (): void {
    $this->withIndex('testing-synchronizes-index', function (): void {
        $action = resolve(SynchronizesIndex::class);
        $index = 'testing-synchronizes-index';

        // Only the given keys are compared, as MeiliSearch merges partial updates.
        $faceting = ['faceting' => ['maxValuesPerFacet' => 10]];
        expect($action($index, $faceting))->toBe(['faceting' => [
            'old' => ['maxValuesPerFacet' => 100],
            'new' => ['maxValuesPerFacet' => 10],
        ]])
            ->and($action($index, $faceting))->toBeEmpty()
        ;

        $typoTolerance = ['typoTolerance' => ['minWordSizeForTypos' => ['oneTypo' => 3]]];
        expect($action($index, $typoTolerance))->toBe(['typoTolerance' => [
            'old' => ['minWordSizeForTypos' => ['oneTypo' => 5]],
            'new' => ['minWordSizeForTypos' => ['oneTypo' => 3]],
        ]])
            ->and($action($index, $typoTolerance))->toBeEmpty()
        ;

        // Facet value sorting always contains a '*' entry, which defaults to 'alpha'.
        $sortFacetValuesBy = ['faceting' => ['sortFacetValuesBy' => ['genres' => 'count']]];
        expect($action($index, $sortFacetValuesBy))->toBe(['faceting' => [
            'old' => ['sortFacetValuesBy' => ['*' => 'alpha']],
            'new' => ['sortFacetValuesBy' => ['*' => 'alpha', 'genres' => 'count']],
        ]])
            ->and($action($index, $sortFacetValuesBy))->toBeEmpty()
        ;

        // Filterable attributes keep their order, while synonyms only have their keys sorted.
        $settings = [
            'filterableAttributes' => ['rank', 'genres'],
            'synonyms'             => ['logan' => ['wolverine', 'james']],
        ];
        expect($action($index, $settings))->toHaveCount(2)
            ->and($action($index, $settings))->toBeEmpty()
        ;
    });
});

/**
 * Test SynchronizesIndex::__invoke() method with an unsupported engine version.
 */
test('with unsupported engine', function (): void {
    expect(fn () => Helpers::throwUnlessSupportedEngine('1.35.9'))
        ->toThrow(MeiliToolsException::class, 'MeiliSearch engine version 1.36.0 or newer is required, found 1.35.9')
    ;

    Helpers::throwUnlessSupportedEngine(Helpers::MINIMUM_ENGINE_VERSION);
    Helpers::throwUnlessSupportedEngine(null);
    Helpers::throwUnlessSupportedEngine(Helpers::engineVersion());
});
