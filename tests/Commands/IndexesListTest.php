<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Tests\TestCase;


/**
 * @internal
 */

/**
 * Test `meili:indexes:list` command with default settings.
 */
test('with default settings', function () {
    $this->withIndex(self::INDEX, function () {
        // Since data returned from MeiliSearch includes microsecond precision timestamps,
        // it's impossible to validate the exact console output.
        $this->artisan('meili:indexes:list')
            // ->expectsOutputToContain(self::INDEX) - Laravel 9 only.
            ->assertSuccessful()
        ;
    });
});

/**
 * Test `meili:indexes:list` command with stats option.
 */
test('with stats', function () {
    $this->withIndex(self::INDEX, function () {
        // Since data returned from MeiliSearch includes microsecond precision timestamps,
        // it's impossible to validate the exact console output.
        $this->artisan('meili:indexes:list', ['--stats' => true])
            // ->expectsOutputToContain(self::INDEX) - Laravel 9 only.
            ->assertSuccessful()
        ;
    });
});
