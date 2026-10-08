<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Illuminate\Support\Arr;

/**
 * Test `meili:model:details` command with default settings.
 */
test('with default settings', function (): void {
    try {
        $values = Helpers::convertIndexDataToTable(Helpers::defaultSettings());

        $this->artisan('meili:model:details')
            ->expectsQuestion('What is the model class?', Movie::class)
            ->expectsTable(['Setting', 'Value'], $values)
            ->assertSuccessful()
        ;

        $this->artisan('meili:model:details', ['model' => Movie::class])
            ->expectsTable(['Setting', 'Value'], $values)
            ->assertSuccessful()
        ;

        $this->artisan('meili:model:details', ['model' => 'Movie'])
            ->expectsTable(['Setting', 'Value'], $values)
            ->assertSuccessful()
        ;
    } finally {
        $this->deleteIndex(resolve(Movie::class)->searchableAs());
    }
});

/**
 * Test `meili:model:details` command with advanced settings.
 */
test('with advanced settings', function (): void {
    try {
        $defaults = Helpers::defaultSettings();
        $settings = resolve(MeiliMovie::class)->meiliSettings();

        $changes = resolve(SynchronizesModel::class)(MeiliMovie::class);
        expect($changes)->not->toBeEmpty();

        $values = Helpers::convertIndexDataToTable(
            Helpers::sortSettings($settings + Arr::only($defaults, ['faceting', 'pagination', 'typoTolerance']))
        );

        $this->artisan('meili:model:details', ['model' => MeiliMovie::class])
            ->expectsTable(['Setting', 'Value'], $values)
            ->assertSuccessful()
        ;
    } finally {
        $this->deleteIndex(resolve(MeiliMovie::class)->searchableAs());
    }
});
