<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\Tools;

/**
 * Test `meili:model:reset` command with advanced settings.
 */
test('with advanced settings', function (): void {
    try {
        $defaults = Helpers::defaultSettings();
        $settings = resolve(MeiliMovie::class)->meiliSettings();

        resolve(SynchronizesModel::class)(MeiliMovie::class);
        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->not->toBe($defaults)
            ->toMatchArray(array_replace($defaults, $settings))
        ;

        $changes = Tools::changes($settings, array_fill_keys(array_keys($settings), null));
        $values = Helpers::convertIndexChangesToTable($changes);

        $this->artisan('meili:model:reset', ['model' => MeiliMovie::class])
            ->expectsTable(['Setting', 'Old', 'New'], $values)
            ->assertSuccessful()
        ;

        $this->artisan('meili:model:reset')
            ->expectsQuestion('What is the model class?', MeiliMovie::class)
            ->expectsTable(['Setting', 'Old', 'New'], [])
            ->assertSuccessful()
        ;

        $this->artisan('meili:model:reset')
            ->expectsQuestion('What is the model class?', 'MeiliMovie')
            ->expectsTable(['Setting', 'Old', 'New'], [])
            ->assertSuccessful()
        ;

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);
    } finally {
        $this->deleteIndex(resolve(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test `meili:model:reset` command with pretend option.
 */
test('with pretend', function (): void {
    try {
        $defaults = Helpers::defaultSettings();
        $settings = resolve(MeiliMovie::class)->meiliSettings();

        resolve(SynchronizesModel::class)(MeiliMovie::class);
        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->not->toBe($defaults)
            ->toMatchArray(array_replace($defaults, $settings))
        ;

        $changes = Tools::changes($settings, array_fill_keys(array_keys($settings), null));
        $values = Helpers::convertIndexChangesToTable($changes);

        $this->artisan('meili:model:reset', ['model' => MeiliMovie::class, '--pretend' => true])
            ->expectsTable(['Setting', 'Old', 'New'], $values)
            ->assertSuccessful()
        ;

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->not->toBe($defaults);
    } finally {
        $this->deleteIndex(resolve(MeiliMovie::class)->searchableAs());
    }
});
