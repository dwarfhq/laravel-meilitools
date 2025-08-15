<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Commands;

use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Dwarf\MeiliTools\Tests\TestCase;
use Illuminate\Support\Arr;

/**
 * @internal
 */
class ModelDetailsTest extends TestCase
{
    /**
     * Test `meili:model:details` command with default settings.
     */
    public function test_with_default_settings(): void
    {
        try {
            $values = Helpers::convertIndexDataToTable(Helpers::defaultSettings(Helpers::engineVersion()));

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
            $this->deleteIndex(app(Movie::class)->searchableAs());
        }
    }

    /**
     * Test `meili:model:details` command with advanced settings.
     */
    public function test_with_advanced_settings(): void
    {
        try {
            $defaults = Helpers::defaultSettings(Helpers::engineVersion());
            $settings = app(MeiliMovie::class)->meiliSettings();

            $changes = $this->app->make(SynchronizesModel::class)(MeiliMovie::class);
            $this->assertNotEmpty($changes);

            $values = Helpers::convertIndexDataToTable(
                Helpers::sortSettings($settings + Arr::only($defaults, ['faceting', 'pagination', 'typoTolerance']))
            );

            $this->artisan('meili:model:details', ['model' => MeiliMovie::class])
                ->expectsTable(['Setting', 'Value'], $values)
                ->assertSuccessful()
            ;
        } finally {
            $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
        }
    }
}
