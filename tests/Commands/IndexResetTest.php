<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Commands;

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\TestCase;
use Dwarf\MeiliTools\Tests\Tools;

/**
 * @internal
 */
class IndexResetTest extends TestCase
{
    /**
     * Test index.
     *
     * @var string
     */
    private const INDEX = 'testing-resets-index';

    /**
     * Test `meili:index:reset` command with advanced settings.
     *
     * @return void
     */
    public function testWithAdvancedSettings(): void
    {
        $this->withIndex(self::INDEX, function () {
            $defaults = Helpers::defaultSettings(Helpers::engineVersion());
            $settings = Tools::movieSettings();

            $this->app->make(SynchronizesIndex::class)(self::INDEX, $settings);
            $details = $this->app->make(DetailsIndex::class)(self::INDEX);
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

            $this->artisan('meili:index:reset', ['index' => self::INDEX])
                ->expectsTable(['Setting', 'Old', 'New'], $values)
                ->assertSuccessful()
            ;

            $this->artisan('meili:index:reset')
                ->expectsQuestion('What is the index name?', self::INDEX)
                ->expectsTable(['Setting', 'Old', 'New'], [])
                ->assertSuccessful()
            ;

            $details = $this->app->make(DetailsIndex::class)(self::INDEX);
            $this->assertSame($defaults, $details);
        });
    }

    /**
     * Test `meili:index:reset` command with pretend option.
     *
     * @return void
     */
    public function testWithPretend(): void
    {
        $this->withIndex(self::INDEX, function () {
            $defaults = Helpers::defaultSettings(Helpers::engineVersion());
            $settings = Tools::movieSettings();

            $this->app->make(SynchronizesIndex::class)(self::INDEX, $settings);
            $details = $this->app->make(DetailsIndex::class)(self::INDEX);
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

            $this->artisan('meili:index:reset', ['index' => self::INDEX, '--pretend' => true])
                ->expectsTable(['Setting', 'Old', 'New'], $values)
                ->assertSuccessful()
            ;

            $details = $this->app->make(DetailsIndex::class)(self::INDEX);
            $this->assertNotSame($defaults, $details);
        });
    }
}
