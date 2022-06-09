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
class ModelsSynchronizeTest extends TestCase
{
    /**
     * Test `meili:models:synchronize` command.
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
            $values = Helpers::convertIndexChangesToTable($changes);

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($defaults, $details);

            $path = __DIR__ . '/../Models';
            $namespace = 'Dwarf\\MeiliTools\\Tests\\Models';
            config(['meilitools.paths' => [$path => $namespace]]);

            $this->artisan('meili:models:synchronize')
                ->expectsOutput('Processed ' . MeiliMovie::class)
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
     * Test `meili:models:synchronize` command.
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
            $values = Helpers::convertIndexChangesToTable($changes);

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($defaults, $details);

            $path = __DIR__ . '/../Models';
            $namespace = 'Dwarf\\MeiliTools\\Tests\\Models';
            config(['meilitools.paths' => [$path => $namespace]]);

            $this->artisan('meili:models:synchronize', ['--dry-run' => true])
                ->expectsOutput('Processed ' . MeiliMovie::class)
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
