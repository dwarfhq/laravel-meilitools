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
    expect(Helpers::guessModelNamespace(Movie::class))->toBe(Movie::class);
    expect(Helpers::guessModelNamespace('Movie'))->toBe(Movie::class);
    expect(Helpers::guessModelNamespace('Fake'))->toBe('Fake');
});
