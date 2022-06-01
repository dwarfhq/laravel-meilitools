<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Feature\Actions;

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\TestCase;

/**
 * @internal
 */
class SynchronizesModelTest extends TestCase
{
    /**
     * Test SynchronizesIndex::__invoke() method with advanced settings.
     *
     * @return void
     */
    public function testWithAdvancedSettings(): void
    {
        try {
            $defaults = Helpers::defaultSettings();
            $settings = MeiliMovie::meiliSettings();
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
            $this->assertSame($settings, $details);
        } finally {
            $this->deleteIndex((new MeiliMovie())->searchableAs());
        }
    }

    /**
     * Test SynchronizesIndex::__invoke() method with dry-run option.
     *
     * @return void
     */
    public function testWithDryRun(): void
    {
        try {
            $defaults = Helpers::defaultSettings();
            $settings = MeiliMovie::meiliSettings();
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
            $this->deleteIndex((new MeiliMovie())->searchableAs());
        }
    }
}
