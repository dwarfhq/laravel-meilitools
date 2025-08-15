<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Dwarf\MeiliTools\Tests\TestCase;

uses(Dwarf\MeiliTools\Tests\TestCase::class);

/**
 * @internal
 */

/**
 * Test Helpers::guessModelNamespace() method.
 */
test('guess model namespace', function () {
    $this->assertSame(Movie::class, Helpers::guessModelNamespace(Movie::class));
    $this->assertSame(Movie::class, Helpers::guessModelNamespace('Movie'));
    $this->assertSame('Fake', Helpers::guessModelNamespace('Fake'));
});
