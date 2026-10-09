<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\Tools;
use Illuminate\Support\Arr;

/**
 * Test `meili:model:synchronize` command with advanced settings.
 */
test('with advanced settings', function (): void {
    try {
        $defaults = Helpers::defaultSettings();
        $settings = resolve(MeiliMovie::class)->meiliSettings();
        $changes = Tools::changes($defaults, $settings);

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);

        $values = Helpers::convertIndexChangesToTable($changes);

        $this->artisan('meili:model:synchronize', ['model' => MeiliMovie::class])
            ->expectsTable(['Setting', 'Old', 'New'], $values)
            ->assertSuccessful()
        ;

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect(Arr::except($details, ['faceting', 'pagination', 'typoTolerance']))->toMatchArray($settings);
    } finally {
        $this->deleteIndex(resolve(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test `meili:model:synchronize` command with pretend option.
 */
test('with pretend', function (): void {
    try {
        $defaults = Helpers::defaultSettings();
        $settings = resolve(MeiliMovie::class)->meiliSettings();
        $changes = Tools::changes($defaults, $settings);

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);

        $values = Helpers::convertIndexChangesToTable($changes);

        $this->artisan('meili:model:synchronize', ['model' => MeiliMovie::class, '--pretend' => true])
            ->expectsTable(['Setting', 'Old', 'New'], $values)
            ->assertSuccessful()
        ;

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);
    } finally {
        $this->deleteIndex(resolve(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test `meili:model:synchronize` command with check option.
 */
test('with check', function (): void {
    try {
        $this->artisan('meili:model:synchronize', ['model' => MeiliMovie::class, '--check' => true])
            ->expectsOutput('Settings are out of sync')
            ->assertFailed()
        ;

        expect(resolve(DetailsModel::class)(MeiliMovie::class))->toMatchArray(Helpers::defaultSettings());

        $this->artisan('meili:model:synchronize', ['model' => MeiliMovie::class])->assertSuccessful();

        $this->artisan('meili:model:synchronize', ['model' => MeiliMovie::class, '--check' => true])
            ->doesntExpectOutput('Settings are out of sync')
            ->assertSuccessful()
        ;
    } finally {
        $this->deleteIndex(resolve(MeiliMovie::class)->searchableAs());
    }
});
