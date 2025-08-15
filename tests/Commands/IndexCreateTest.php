<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Commands;

use Dwarf\MeiliTools\Tests\TestCase;

/**
 * @internal
 */
class IndexCreateTest extends TestCase
{
    /**
     * Test index.
     *
     * @var string
     */
    private const INDEX = 'testing-create-index';

    /**
     * Test `meili:index:create` command with default settings.
     */
    public function test_with_default_settings(): void
    {
        try {
            $this->artisan('meili:index:create')
                ->expectsQuestion('What is the index name?', self::INDEX)
                ->assertSuccessful()
            ;
        } finally {
            $this->deleteIndex(self::INDEX);
        }

        try {
            $this->artisan('meili:index:create', ['index' => self::INDEX])
                ->assertSuccessful()
            ;
        } finally {
            $this->deleteIndex(self::INDEX);
        }
    }
}
