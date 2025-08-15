<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Commands;

use Dwarf\MeiliTools\Tests\Models\Movie;
use Dwarf\MeiliTools\Tests\TestCase;

/**
 * @internal
 */
class ModelViewTest extends TestCase
{
    /**
     * Test `meili:model:view` command with default settings.
     */
    public function test_with_default_settings(): void
    {
        $index = app(Movie::class)->searchableAs();

        try {
            // Since data returned from MeiliSearch includes microsecond precision timestamps,
            // it's impossible to validate the exact console output.
            $this->artisan('meili:model:view')
                ->expectsQuestion('What is the model class?', Movie::class)
                // ->expectsOutputToContain($index) - Laravel 9 only.
                ->assertSuccessful()
            ;

            $this->artisan('meili:model:view', ['model' => Movie::class])
                // ->expectsOutputToContain($index) - Laravel 9 only.
                ->assertSuccessful()
            ;

            $this->artisan('meili:model:view', ['model' => 'Movie'])
                // ->expectsOutputToContain($index) - Laravel 9 only.
                ->assertSuccessful()
            ;
        } finally {
            $this->deleteIndex($index);
        }
    }

    /**
     * Test `meili:model:view` command with stats option.
     */
    public function test_with_stats(): void
    {
        $index = app(Movie::class)->searchableAs();

        try {
            // Since data returned from MeiliSearch includes microsecond precision timestamps,
            // it's impossible to validate the exact console output.
            $this->artisan('meili:model:view', ['model' => Movie::class, '--stats' => true])
                // ->expectsOutputToContain($index) - Laravel 9 only.
                ->assertSuccessful()
            ;
        } finally {
            $this->deleteIndex($index);
        }
    }
}
