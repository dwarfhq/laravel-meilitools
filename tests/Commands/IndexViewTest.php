<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Tests\TestCase;


/**
 * @internal
 */

/**
 * Test `meili:index:view` command with default settings.
 */
test('with default settings', function () {
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
});

/**
 * Test `meili:index:view` command with stats option.
 */
test('with stats', function () {
    $this->withIndex(self::INDEX, function () {
        // Since data returned from MeiliSearch includes microsecond precision timestamps,
        // it's impossible to validate the exact console output.
        $this->artisan('meili:index:view', ['index' => self::INDEX, '--stats' => true])
            // ->expectsOutputToContain(self::INDEX) - Laravel 9 only.
            ->assertSuccessful()
        ;
    });
});
