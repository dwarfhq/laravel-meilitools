<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\ListsClasses;
use Dwarf\MeiliTools\Contracts\Indexes\MeiliSettings;
use Dwarf\MeiliTools\Tests\TestCase;

uses(Dwarf\MeiliTools\Tests\TestCase::class);

/**
 * @internal
 */

/**
 * Test listing models using absolute and relative paths.
 */
test('path listing', function () {
    $action = app()->make(ListsClasses::class);

    $path = 'app/Models';
    $namespace = 'App\\Models';
    $classes = $action($path, $namespace);
    $this->assertCount(0, $classes);

    $path = base_path($path);
    $classes = $action($path, $namespace);
    $this->assertCount(0, $classes);

    $path = __DIR__ . '/../Models';
    $namespace = 'Dwarf\\MeiliTools\\Tests\\Models';
    $classes = $action($path, $namespace);
    $this->assertCount(3, $classes);

    $classes = $action($path, $namespace, fn ($class) => is_a($class, MeiliSettings::class, true));
    $this->assertCount(2, $classes);
});
