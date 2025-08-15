<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Tests\Models\Movie;

/**
 * Test `meili:model:view` command with default settings.
 */
test('with default settings', function () {
    $index = app(Movie::class)->searchableAs();

    try {
        // Since data returned from MeiliSearch includes microsecond precision timestamps,
        // it's impossible to validate the exact console output.
        $this->artisan('meili:model:view')
            ->expectsQuestion('What is the model class?', Movie::class)
            ->expectsOutputToContain($index)
            ->assertSuccessful()
        ;

        $this->artisan('meili:model:view', ['model' => Movie::class])
            ->expectsOutputToContain($index)
            ->assertSuccessful()
        ;

        $this->artisan('meili:model:view', ['model' => 'Movie'])
            ->expectsOutputToContain($index)
            ->assertSuccessful()
        ;
    } finally {
        $this->deleteIndex($index);
    }
});

/**
 * Test `meili:model:view` command with stats option.
 */
test('with stats', function () {
    $index = app(Movie::class)->searchableAs();

    try {
        // Since data returned from MeiliSearch includes microsecond precision timestamps,
        // it's impossible to validate the exact console output.
        $this->artisan('meili:model:view', ['model' => Movie::class, '--stats' => true])
            ->expectsOutputToContain($index)
            ->assertSuccessful()
        ;
    } finally {
        $this->deleteIndex($index);
    }
});
