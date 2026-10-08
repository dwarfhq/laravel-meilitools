<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Tools;
use Illuminate\Support\Arr;

/**
 * Test `meili:index:details` command with default settings.
 */
test('with default settings', function (): void {
    $this->withIndex('testing-details-index', function (): void {
        $values = Helpers::convertIndexDataToTable(Helpers::defaultSettings());

        $this->artisan('meili:index:details')
            ->expectsQuestion('What is the index name?', 'testing-details-index')
            ->expectsTable(['Setting', 'Value'], $values)
            ->assertSuccessful()
        ;

        $this->artisan('meili:index:details', ['index' => 'testing-details-index'])
            ->expectsTable(['Setting', 'Value'], $values)
            ->assertSuccessful()
        ;
    });
});

/**
 * Test `meili:index:details` command with advanced settings.
 */
test('with advanced settings', function (): void {
    $this->withIndex('testing-details-index', function (): void {
        $defaults = Helpers::defaultSettings();
        $settings = Tools::movieSettings();

        $changes = resolve(SynchronizesIndex::class)('testing-details-index', $settings);
        expect($changes)->not->toBeEmpty();

        $values = Helpers::convertIndexDataToTable(
            Helpers::sortSettings($settings + Arr::only($defaults, ['faceting', 'pagination', 'typoTolerance']))
        );

        $this->artisan('meili:index:details', ['index' => 'testing-details-index'])
            ->expectsTable(['Setting', 'Value'], $values)
            ->assertSuccessful()
        ;
    });
});
