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
    /**
     * {@inheritdoc}
     */
    public function __invoke(string $path, string $namespace, ?callable $filter = null): array
    {
        // Use path as-is if absolute, otherwise load relative to the project base path.
        $files = scandir(Str::startsWith($path, '/') ? $path : base_path($path));

        $classes = collect($files)
            ->filter(fn ($file) => Str::endsWith($file, '.php'))
            ->map(fn ($file) => Str::finish($namespace, '\\') . basename($file, '.php'))
        ;
        if ($filter) {
            $classes = $classes->filter($filter);
        }

        return $classes->all();
    }
}
