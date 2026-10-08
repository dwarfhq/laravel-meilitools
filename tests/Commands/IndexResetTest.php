<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Tools;

/**
 * Test `meili:index:reset` command with advanced settings.
 */
test('with advanced settings', function (): void {
    $this->withIndex('testing-resets-index', function (): void {
        $defaults = Helpers::defaultSettings();
        $settings = Tools::movieSettings();

        resolve(SynchronizesIndex::class)('testing-resets-index', $settings);
        $details = resolve(DetailsIndex::class)('testing-resets-index');
        expect($details)->not->toBe($defaults)
            ->toMatchArray(array_replace($defaults, $settings))
        ;

        $changes = Tools::changes($settings, array_fill_keys(array_keys($settings), null));
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

        $details = resolve(DetailsIndex::class)('testing-resets-index');
        expect($details)->toMatchArray($defaults);
    });
});

/**
 * Test `meili:index:reset` command with pretend option.
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

        $changes = Tools::changes($settings, array_fill_keys(array_keys($settings), null));
        $values = Helpers::convertIndexChangesToTable($changes);

        $this->artisan('meili:index:reset', ['index' => 'testing-resets-index', '--pretend' => true])
            ->expectsTable(['Setting', 'Old', 'New'], $values)
            ->assertSuccessful()
        ;

        $details = resolve(DetailsIndex::class)('testing-resets-index');
        expect($details)->not->toBe($defaults);
    });
});
