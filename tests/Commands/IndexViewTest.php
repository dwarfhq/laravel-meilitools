<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Commands;

use Dwarf\MeiliTools\Tests\TestCase;

/**
 * @internal
 */
class IndexViewTest extends TestCase
{
    /**
     * Test index.
     *
     * @var string
     */
    private const INDEX = 'testing-index-view';

    /**
     * Test `meili:index:view` command with default settings.
     */
    public function test_with_default_settings(): void
    {
        $this->withIndex(self::INDEX, function () {
            // Since data returned from MeiliSearch includes microsecond precision timestamps,
            // it's impossible to validate the exact console output.
            $this->artisan('meili:index:view')
                ->expectsQuestion('What is the index name?', self::INDEX)
                // ->expectsOutputToContain(self::INDEX) - Laravel 9 only.
                ->assertSuccessful()
            ;

            $this->artisan('meili:index:view', ['index' => self::INDEX])
                // ->expectsOutputToContain(self::INDEX) - Laravel 9 only.
                ->assertSuccessful()
            ;
        });
    }

    /**
     * Test `meili:index:view` command with stats option.
     */
    public function test_with_stats(): void
    {
        $this->withIndex(self::INDEX, function () {
            // Since data returned from MeiliSearch includes microsecond precision timestamps,
            // it's impossible to validate the exact console output.
            $this->artisan('meili:index:view', ['index' => self::INDEX, '--stats' => true])
                // ->expectsOutputToContain(self::INDEX) - Laravel 9 only.
                ->assertSuccessful()
            ;
        });
    }
}
