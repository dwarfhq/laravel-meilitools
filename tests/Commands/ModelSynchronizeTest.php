<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Commands;

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\TestCase;

/**
 * @internal
 */
class ModelSynchronizeTest extends TestCase
{
    /**
     * Test `meili:model:synchronize` command with advanced settings.
     *
     * @return void
     */
    public function testWithAdvancedSettings(): void
    {
        try {
            $defaults = Helpers::defaultSettings($this->engineVersion());
            $settings = MeiliMovie::meiliSettings();
            $changes = collect($settings)
                ->mapWithKeys(function ($value, $key) use ($defaults) {
                    $old = $defaults[$key];
                    $new = $value;

                    return [$key => $old === $new ? false : compact('old', 'new')];
                })
                ->filter()
                ->all()
            ;

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($defaults, $details);

            $values = Helpers::convertIndexChangesToTable($changes);

            $this->artisan('meili:model:synchronize', ['model' => MeiliMovie::class])
                ->expectsTable(['Setting', 'Old', 'New'], $values)
                ->assertSuccessful()
            ;

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($settings, $details);
        } finally {
            $this->deleteIndex((new MeiliMovie())->searchableAs());
        }
    }

    /**
     * Test `meili:model:synchronize` command with dry-run option.
     *
     * @return void
     */
    public function testWithDryRun(): void
    {
        try {
            $defaults = Helpers::defaultSettings($this->engineVersion());
            $settings = MeiliMovie::meiliSettings();
            $changes = collect($settings)
                ->mapWithKeys(function ($value, $key) use ($defaults) {
                    $old = $defaults[$key];
                    $new = $value;

                    return [$key => $old === $new ? false : compact('old', 'new')];
                })
                ->filter()
                ->all()
            ;

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($defaults, $details);

            $values = Helpers::convertIndexChangesToTable($changes);

            $this->artisan('meili:model:synchronize', ['model' => MeiliMovie::class, '--dry-run' => true])
                ->expectsTable(['Setting', 'Old', 'New'], $values)
                ->assertSuccessful()
            ;

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($defaults, $details);
        } finally {
            $this->deleteIndex((new MeiliMovie())->searchableAs());
        }
    }
}
