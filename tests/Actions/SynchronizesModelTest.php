<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Actions;

use BadMethodCallException;
use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Dwarf\MeiliTools\Tests\TestCase;
use Illuminate\Support\Arr;

/**
 * @internal
 */
class SynchronizesModelTest extends TestCase
{
    /**
     * Test SynchronizesModel::__invoke() method with invalid model.
     *
     * @return void
     */
    public function testWithInvalidModel(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method ' . Movie::class . '::meiliSettings()');

        $this->app->make(SynchronizesModel::class)(Movie::class);
    }

    /**
     * Test SynchronizesModel::__invoke() method with advanced settings.
     *
     * @return void
     */
    public function testWithAdvancedSettings(): void
    {
        try {
            $defaults = Helpers::defaultSettings($this->engineVersion());
            $settings = app(MeiliMovie::class)->meiliSettings();
            $expected = collect($settings)
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

            $changes = $this->app->make(SynchronizesModel::class)(MeiliMovie::class);
            $this->assertSame($expected, $changes);

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($settings, Arr::except($details, ['typoTolerance']));
        } finally {
            $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
        }
    }

    /**
     * Test SynchronizesModel::__invoke() method with dry-run option.
     *
     * @return void
     */
    public function testWithDryRun(): void
    {
        try {
            $defaults = Helpers::defaultSettings($this->engineVersion());
            $settings = app(MeiliMovie::class)->meiliSettings();
            $expected = collect($settings)
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

            $changes = $this->app->make(SynchronizesModel::class)(MeiliMovie::class, true);
            $this->assertSame($expected, $changes);

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($defaults, $details);
        } finally {
            $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
        }
    }
}
