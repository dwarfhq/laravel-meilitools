<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Commands;

use Dwarf\MeiliTools\Tests\TestCase;

/**
 * @internal
 */
class IndexDeleteTest extends TestCase
{
    /**
     * Test index.
     *
     * @var string
     */
    private const INDEX = 'testing-delete-index';

    /**
     * Test `meili:index:delete` command with default settings.
     *
     * @return void
     */
    public function testWithDefaultSettings(): void
    {
        $this->withIndex(self::INDEX, function () {
            $this->artisan('meili:index:delete')
                ->expectsQuestion('What is the index name?', self::INDEX)
                ->expectsConfirmation('Are you sure you wish to permanently delete the '.self::INDEX.' index?', 'no')
                ->assertFailed()
            ;

            $this->artisan('meili:index:delete')
                ->expectsQuestion('What is the index name?', self::INDEX)
                ->expectsConfirmation('Are you sure you wish to permanently delete the '.self::INDEX.' index?', 'yes')
                ->assertSuccessful()
            ;
        });
    }

    /**
     * Test `meili:index:delete` command with specified name.
     *
     * @return void
     */
    public function testWithSpecifiedName(): void
    {
        $this->withIndex(self::INDEX, function () {
            $this->artisan('meili:index:delete', ['index' => self::INDEX])
                ->expectsConfirmation('Are you sure you wish to permanently delete the '.self::INDEX.' index?', 'no')
                ->assertFailed()
            ;

            $this->artisan('meili:index:delete', ['index' => self::INDEX])
                ->expectsConfirmation('Are you sure you wish to permanently delete the '.self::INDEX.' index?', 'yes')
                ->assertSuccessful()
            ;
        });
    }
}
