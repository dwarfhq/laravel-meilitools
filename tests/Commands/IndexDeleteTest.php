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
     */
    public function test_with_default_settings(): void
    {
        $this->withIndex(self::INDEX, function () {
            $this->artisan('meili:index:delete')
                ->expectsQuestion('What is the index name?', self::INDEX)
                ->expectsConfirmation('Are you sure you want to run this command?', 'no')
                ->assertFailed()
            ;

            $this->artisan('meili:index:delete')
                ->expectsQuestion('What is the index name?', self::INDEX)
                ->expectsConfirmation('Are you sure you want to run this command?', 'yes')
                ->assertSuccessful()
            ;
        });
    }

    /**
     * Test `meili:index:delete` command with specified name.
     */
    public function test_with_specified_name(): void
    {
        $this->withIndex(self::INDEX, function () {
            $this->artisan('meili:index:delete', ['index' => self::INDEX])
                ->expectsConfirmation('Are you sure you want to run this command?', 'no')
                ->assertFailed()
            ;

            $this->artisan('meili:index:delete', ['index' => self::INDEX])
                ->expectsConfirmation('Are you sure you want to run this command?', 'yes')
                ->assertSuccessful()
            ;
        });
    }

    /**
     * Test `meili:index:delete` command with force option.
     */
    public function test_with_force_option(): void
    {
        $this->withIndex(self::INDEX, function () {
            $this->artisan('meili:index:delete', ['index' => self::INDEX, '--force' => true])
                ->assertSuccessful()
            ;
        });
    }
}
