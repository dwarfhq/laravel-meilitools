<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\ListsClasses;
use Dwarf\MeiliTools\Contracts\Indexes\MeiliSettings;

/**
 * Test listing models using absolute and relative paths.
 */
test('path listing', function (): void {
    $action = resolve(ListsClasses::class);

    $path = 'app/Models';
    $namespace = 'App\\Models';
    $classes = $action($path, $namespace);
    expect($classes)->toBeEmpty();

    $path = base_path($path);
    $classes = $action($path, $namespace);
    expect($classes)->toBeEmpty();

    $path = __DIR__ . '/../Models';
    $namespace = 'Dwarf\\MeiliTools\\Tests\\Models';
    $classes = $action($path, $namespace);
    expect($classes)->toHaveCount(3);

    $classes = $action($path, $namespace, fn ($class): bool => is_a($class, MeiliSettings::class, true));
    expect($classes)->toHaveCount(2);
});
