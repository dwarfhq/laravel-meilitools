<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Commands;

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\TestCase;

/**
 * @internal
 */
class ModelResetTest extends TestCase
{
    /**
     * Test `meili:model:reset` command with advanced settings.
     *
     * @return void
     */
    public function testWithAdvancedSettings(): void
    {
        try {
            $defaults = Helpers::defaultSettings(Helpers::engineVersion());
            $settings = app(MeiliMovie::class)->meiliSettings();

            $this->app->make(SynchronizesModel::class)(MeiliMovie::class);
            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertNotSame($defaults, $details);
            $this->assertSame(array_replace($defaults, $settings), $details);

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

            $this->artisan('meili:model:reset', ['model' => MeiliMovie::class])
                ->expectsTable(['Setting', 'Old', 'New'], $values)
                ->assertSuccessful()
            ;

            $this->artisan('meili:model:reset')
                ->expectsQuestion('What is the model class?', MeiliMovie::class)
                ->expectsTable(['Setting', 'Old', 'New'], [])
                ->assertSuccessful()
            ;

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($defaults, $details);
        } finally {
            $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
        }
    }

    /**
     * Test `meili:model:reset` command with pretend option.
     *
     * @return void
     */
    public function testWithPretend(): void
    {
        try {
            $defaults = Helpers::defaultSettings(Helpers::engineVersion());
            $settings = app(MeiliMovie::class)->meiliSettings();

            $this->app->make(SynchronizesModel::class)(MeiliMovie::class);
            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertNotSame($defaults, $details);
            $this->assertSame(array_replace($defaults, $settings), $details);

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

            $this->artisan('meili:model:reset', ['model' => MeiliMovie::class, '--pretend' => true])
                ->expectsTable(['Setting', 'Old', 'New'], $values)
                ->assertSuccessful()
            ;

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertNotSame($defaults, $details);
        } finally {
            $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
        }
    }
}
