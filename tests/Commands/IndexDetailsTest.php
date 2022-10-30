<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Commands;

use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\TestCase;
use Dwarf\MeiliTools\Tests\Tools;
use Illuminate\Support\Arr;

/**
 * @internal
 */
class IndexDetailsTest extends TestCase
{
    /**
     * Test index.
     *
     * @var string
     */
    private const INDEX = 'testing-details-index';

    /**
     * Test `meili:index:details` command with default settings.
     *
     * @return void
     */
    public function testWithDefaultSettings(): void
    {
        $this->withIndex(self::INDEX, function () {
            $values = Helpers::convertIndexDataToTable(Helpers::defaultSettings(Helpers::engineVersion()));

            $this->artisan('meili:index:details')
                ->expectsQuestion('What is the index name?', self::INDEX)
                ->expectsTable(['Setting', 'Value'], $values)
                ->assertSuccessful()
            ;

            $this->artisan('meili:index:details', ['index' => self::INDEX])
                ->expectsTable(['Setting', 'Value'], $values)
                ->assertSuccessful()
            ;
        });
    }

    /**
     * Test `meili:index:details` command with advanced settings.
     *
     * @return void
     */
    public function testWithAdvancedSettings(): void
    {
        $this->withIndex(self::INDEX, function () {
            $defaults = Helpers::defaultSettings(Helpers::engineVersion());
            $settings = Tools::movieSettings();

            $changes = $this->app->make(SynchronizesIndex::class)(self::INDEX, $settings);
            $this->assertNotEmpty($changes);

            $values = Helpers::convertIndexDataToTable(
                Helpers::sortSettings($settings + Arr::only($defaults, ['faceting', 'pagination', 'typoTolerance']))
            );

            $this->artisan('meili:index:details', ['index' => self::INDEX])
                ->expectsTable(['Setting', 'Value'], $values)
                ->assertSuccessful()
            ;
        });
    }
}
