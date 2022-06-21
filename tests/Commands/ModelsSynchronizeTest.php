<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Commands;

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\BrokenMovie;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

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
            $settings = app(MeiliMovie::class)->meiliSettings();
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
                ->expectsOutput('Processed ' . BrokenMovie::class)
                ->expectsOutput(sprintf(
                    "Exception '%s' with message '%s'",
                    ValidationException::class,
                    'The distinct attribute must be a string.'
                ))
                ->expectsOutput('Processed ' . MeiliMovie::class)
                ->expectsTable(['Setting', 'Old', 'New'], $values)
                ->assertSuccessful()
            ;

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($settings, Arr::except($details, ['typoTolerance']));
        } finally {
            $this->deleteIndex(app(BrokenMovie::class)->searchableAs());
            $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
        }
    }

    /**
     * Test `meili:models:synchronize` command with pretend option.
     *
     * @return void
     */
    public function testWithPretend(): void
    {
        try {
            $defaults = Helpers::defaultSettings($this->engineVersion());
            $settings = app(MeiliMovie::class)->meiliSettings();
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

            $this->artisan('meili:models:synchronize', ['--pretend' => true])
                ->expectsOutput('Processed ' . BrokenMovie::class)
                ->expectsOutput(sprintf(
                    "Exception '%s' with message '%s'",
                    ValidationException::class,
                    'The distinct attribute must be a string.'
                ))
                ->expectsOutput('Processed ' . MeiliMovie::class)
                ->expectsTable(['Setting', 'Old', 'New'], $values)
                ->assertSuccessful()
            ;

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($defaults, $details);
        } finally {
            $this->deleteIndex(app(BrokenMovie::class)->searchableAs());
            $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
        }
    }
}
