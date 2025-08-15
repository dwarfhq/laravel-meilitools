<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Tests\TestCase;

uses(Dwarf\MeiliTools\Tests\TestCase::class);

/**
 * @internal
 */

/**
 * Test `meili:index:create` command with default settings.
 */
test('with default settings', function () {
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
});
