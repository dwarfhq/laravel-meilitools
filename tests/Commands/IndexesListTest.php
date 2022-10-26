<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Commands;

use Dwarf\MeiliTools\Tests\TestCase;

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
            $this->artisan('meili:indexes:list')
                // ->expectsTable(['Setting', 'Value'], $values) // Can’t test the exact table values without knowing exact details about the index.
                ->assertSuccessful()
            ;
        });
    }
}
