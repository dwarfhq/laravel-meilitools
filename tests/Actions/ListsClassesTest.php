<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Actions\ListClasses;
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

/**
 * Test ListsClasses::__invoke() method with a missing path.
 */
test('missing path', function (): void {
    expect(resolve(ListsClasses::class)('app/Missing', 'App\\Missing'))->toBeEmpty();
});

/**
 * Test ListsClasses detecting absolute paths, including Windows paths.
 */
test('absolute paths', function (string $path, bool $expected): void {
    $action = new class extends ListClasses
    {
        public function absolute(string $path): bool
        {
            return $this->isAbsolutePath($path);
        }
    };

    expect($action->absolute($path))->toBe($expected);
})->with([
    'unix'           => ['/var/www/app/Models', true],
    'windows drive'  => ['C:\\www\\app\\Models', true],
    'windows slash'  => ['c:/www/app/Models', true],
    'unc'            => ['\\\\server\\share\\Models', true],
    'relative'       => ['app/Models', false],
    'drive relative' => ['C:Models', false],
]);
