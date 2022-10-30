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
     *
     * @return void
     */
    public function testWithDefaultSettings(): void
    {
        $index = app(Movie::class)->searchableAs();

        try {
            $this->artisan('meili:model:view')
                ->expectsQuestion('What is the model class?', Movie::class)
                ->expectsOutputToContain($index)
                ->assertSuccessful()
            ;

            $this->artisan('meili:model:view', ['model' => Movie::class])
                ->expectsOutputToContain($index)
                ->assertSuccessful()
            ;
        } finally {
            $this->deleteIndex($index);
        }
    }

    /**
     * Test `meili:model:view` command with stats option.
     *
     * @return void
     */
    public function testWithStats(): void
    {
        $index = app(Movie::class)->searchableAs();

        try {
            $this->artisan('meili:model:view', ['model' => Movie::class, '--stats' => true])
                ->expectsOutputToContain($index)
                ->assertSuccessful()
            ;
        } finally {
            $this->deleteIndex($index);
        }
    }
}
