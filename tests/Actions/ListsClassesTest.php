<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\ListsClasses;
use Dwarf\MeiliTools\Contracts\Indexes\MeiliSettings;

/**
 * Test listing models using absolute and relative paths.
 */
test('path listing', function () {
    $action = app()->make(ListsClasses::class);

    $path = 'app/Models';
    $namespace = 'App\\Models';
    $classes = $action($path, $namespace);
    expect($classes)->toHaveCount(0);

    $path = base_path($path);
    $classes = $action($path, $namespace);
    expect($classes)->toHaveCount(0);

    $path = __DIR__ . '/../Models';
    $namespace = 'Dwarf\\MeiliTools\\Tests\\Models';
    $classes = $action($path, $namespace);
    expect($classes)->toHaveCount(3);

    $classes = $action($path, $namespace, fn ($class) => is_a($class, MeiliSettings::class, true));
    expect($classes)->toHaveCount(2);
});
