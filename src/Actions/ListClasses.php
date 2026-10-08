<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ListsClasses;
use Illuminate\Support\Str;

/**
 * List classes.
 */
class ListClasses implements ListsClasses
{
    public function __invoke(string $path, string $namespace, ?callable $filter = null): array
    {
        $directory = Str::startsWith($path, '/') ? $path : base_path($path);
        if (!is_dir($directory)) {
            return [];
        }

        $files = scandir($directory) ?: [];

        $classes = array_map(
            fn (string $file): string => Str::finish($namespace, '\\') . basename($file, '.php'),
            array_filter($files, fn (string $file): bool => Str::endsWith($file, '.php')),
        );

        return array_values($filter !== null ? array_filter($classes, $filter) : $classes);
    }
}
