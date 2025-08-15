<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Tools;

/**
 * Test `meili:index:reset` command with advanced settings.
 */
test('with advanced settings', function () {
    $this->withIndex('testing-resets-index', function () {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = Tools::movieSettings();

        app()->make(SynchronizesIndex::class)('testing-resets-index', $settings);
        $details = app()->make(DetailsIndex::class)('testing-resets-index');
        expect($details)->not->toBe($defaults);
        expect($details)->toMatchArray(array_replace($defaults, $settings));

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

        $this->artisan('meili:index:reset', ['index' => 'testing-resets-index'])
            ->expectsTable(['Setting', 'Old', 'New'], $values)
            ->assertSuccessful()
        ;

        $this->artisan('meili:index:reset')
            ->expectsQuestion('What is the index name?', 'testing-resets-index')
            ->expectsTable(['Setting', 'Old', 'New'], [])
            ->assertSuccessful()
        ;

        $details = app()->make(DetailsIndex::class)('testing-resets-index');
        expect($details)->toMatchArray($defaults);
    });
});

/**
 * Test `meili:index:reset` command with pretend option.
 */
test('with pretend', function () {
    $this->withIndex('testing-resets-index', function () {
        $defaults = Helpers::defaultSettings(Helpers::engineVersion());
        $settings = Tools::movieSettings();

        app()->make(SynchronizesIndex::class)('testing-resets-index', $settings);
        $details = app()->make(DetailsIndex::class)('testing-resets-index');
        expect($details)->not->toBe($defaults);
        expect($details)->toMatchArray(array_replace($defaults, $settings));

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

        $this->artisan('meili:index:reset', ['index' => 'testing-resets-index', '--pretend' => true])
            ->expectsTable(['Setting', 'Old', 'New'], $values)
            ->assertSuccessful()
        ;

        $details = app()->make(DetailsIndex::class)('testing-resets-index');
        expect($details)->not->toBe($defaults);
    });
});
