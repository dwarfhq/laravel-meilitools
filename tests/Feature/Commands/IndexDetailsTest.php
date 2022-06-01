<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Feature\Commands;

use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\TestCase;
use Dwarf\MeiliTools\Tests\Tools;
use Illuminate\Support\Str;

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
            $values = collect(Helpers::defaultSettings())
                ->map(function ($value, $setting) {
                    return [
                        (string) Str::of($setting)->snake()->replace('_', ' ')->title(),
                        Helpers::export($value),
                    ];
                })
                ->values()
                ->all()
            ;

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
            $settings = Tools::movieSettings();

            $action = $this->app->make(SynchronizesIndex::class);
            $changes = ($action)(self::INDEX, $settings);
            $this->assertNotEmpty($changes);

            $values = collect($settings)
                ->map(function ($value, $setting) {
                    return [
                        (string) Str::of($setting)->snake()->replace('_', ' ')->title(),
                        Helpers::export($value),
                    ];
                })
                ->values()
                ->all()
            ;

            $this->artisan('meili:index:details', ['index' => self::INDEX])
                ->expectsTable(['Setting', 'Value'], $values)
                ->assertSuccessful()
            ;
        });
    }
}
