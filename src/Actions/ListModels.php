<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ListsClasses;
use Dwarf\MeiliTools\Contracts\Actions\ListsModels;
use Dwarf\MeiliTools\Contracts\Indexes\MeiliSettings;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Database\Eloquent\Model;

/**
 * List models with MeiliSearch index settings.
 */
class ListModels implements ListsModels
{
    public function __construct(protected ListsClasses $listClasses)
    {
    }

    public function __invoke(): array
    {
        $configured = Helpers::scoutModels();
        $filter = fn (string $class): bool => is_a($class, MeiliSettings::class, true)
            || \in_array($class, $configured, true);

        /** @var array<string, string> $paths */
        $paths = config('meilitools.paths', []);
        /** @var list<class-string<Model>> $classes */
        $classes = collect($paths)
            ->flatMap(fn (string $namespace, string $path): array => ($this->listClasses)($path, $namespace, $filter))
            ->merge($configured)
            ->unique()
            ->values()
            ->all()
        ;

        return $classes;
    }
}
