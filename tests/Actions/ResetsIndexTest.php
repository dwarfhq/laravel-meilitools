<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Actions;

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\ResetsIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\TestCase;
use Dwarf\MeiliTools\Tests\Tools;

/**
 * @internal
 */
class ResetsIndexTest extends TestCase
{
    /**
     * Test index.
     *
     * @var string
     */
    private const INDEX = 'testing-resets-index';

    /**
     * Test ResetsIndex::__invoke() method with movie settings.
     */
    public function test_with_movie_settings(): void
    {
        $this->withIndex(self::INDEX, function () {
            $defaults = Helpers::defaultSettings(Helpers::engineVersion());
            $settings = Tools::movieSettings();

            $this->app->make(SynchronizesIndex::class)(self::INDEX, $settings);
            $details = $this->app->make(DetailsIndex::class)(self::INDEX);
            $this->assertNotSame($defaults, $details);
            $this->assertSame(array_replace($defaults, $settings), $details);

            $changes = $this->app->make(ResetsIndex::class)(self::INDEX);
            $this->assertCount(8, $changes);

            foreach ($changes as $key => $value) {
                $old = $settings[$key];
                $new = null;
                $this->assertSame(compact('old', 'new'), $value);
            }

            $details = $this->app->make(DetailsIndex::class)(self::INDEX);
            $this->assertSame($defaults, $details);
        });
    }

    /**
     * Test ResetsIndex::__invoke() method with pretend.
     */
    public function test_with_pretend(): void
    {
        $this->withIndex(self::INDEX, function () {
            $defaults = Helpers::defaultSettings(Helpers::engineVersion());
            $settings = Tools::movieSettings();

            $this->app->make(SynchronizesIndex::class)(self::INDEX, $settings);
            $details = $this->app->make(DetailsIndex::class)(self::INDEX);
            $this->assertNotSame($defaults, $details);
            $this->assertSame(array_replace($defaults, $settings), $details);

            $changes = $this->app->make(ResetsIndex::class)(self::INDEX, true);
            $this->assertCount(8, $changes);

            foreach ($changes as $key => $value) {
                $old = $settings[$key];
                $new = null;
                $this->assertSame(compact('old', 'new'), $value);
            }

            $details = $this->app->make(DetailsIndex::class)(self::INDEX);
            $this->assertNotSame($defaults, $details);
        });
    }
}
