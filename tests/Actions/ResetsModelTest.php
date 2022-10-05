<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Actions;

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\ResetsModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\TestCase;

/**
 * @internal
 */
class ResetsModelTest extends TestCase
{
    /**
     * Test ResetsModel::__invoke() method with advanced settings.
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

            $changes = $this->app->make(ResetsModel::class)(MeiliMovie::class);
            $this->assertCount(8, $changes);

            foreach ($changes as $key => $value) {
                $old = $settings[$key];
                $new = null;
                $this->assertSame(compact('old', 'new'), $value);
            }

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($defaults, $details);
        } finally {
            $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
        }
    }

    /**
     * Test ResetsModel::__invoke() method with pretend option.
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

            $changes = $this->app->make(ResetsModel::class)(MeiliMovie::class, true);
            $this->assertCount(8, $changes);

            foreach ($changes as $key => $value) {
                $old = $settings[$key];
                $new = null;
                $this->assertSame(compact('old', 'new'), $value);
            }

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertNotSame($defaults, $details);
        } finally {
            $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
        }
    }

    /**
     * Test ResetsModel::__invoke() method with short model name.
     *
     * @return void
     */
    public function testWithShortModelName(): void
    {
        $path = __DIR__ . '/../Models';
        $namespace = 'Dwarf\\MeiliTools\\Tests\\Models';
        config(['meilitools.paths' => [$path => $namespace]]);

        try {
            $defaults = Helpers::defaultSettings(Helpers::engineVersion());
            $settings = app(MeiliMovie::class)->meiliSettings();

            $this->app->make(SynchronizesModel::class)('MeiliMovie');
            $details = $this->app->make(DetailsModel::class)('MeiliMovie');
            $this->assertNotSame($defaults, $details);
            $this->assertSame(array_replace($defaults, $settings), $details);

            $changes = $this->app->make(ResetsModel::class)('MeiliMovie', true);
            $this->assertCount(8, $changes);

            foreach ($changes as $key => $value) {
                $old = $settings[$key];
                $new = null;
                $this->assertSame(compact('old', 'new'), $value);
            }

            $details = $this->app->make(DetailsModel::class)('MeiliMovie');
            $this->assertNotSame($defaults, $details);
        } finally {
            $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
        }
    }
}
