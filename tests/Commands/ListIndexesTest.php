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
class ListIndexesTest extends TestCase
{
    /**
     * Test index.
     *
     * @var string
     */
    private const INDEX = 'testing-index-list';

    /**
     * Test `meili:index:list` command with default settings.
     *
     * @return void
     */
    public function testWithDefaultSettings(): void
    {
        $this->withIndex(self::INDEX, function () {
            $this->artisan('meili:index:list')
                // ->expectsTable(['Setting', 'Value'], $values) // Can’t test the exact table values without knowing exact details about the index.
                ->assertSuccessful()
            ;
        });
    }
}
