<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Lists classes.
 */
interface ListsClasses
{
    /**
     * Get a list of classes in the given path.
     *
     * @param (callable(string): bool)|null $filter
     *
     * @return list<string>
     */
    public function __invoke(string $path, string $namespace, ?callable $filter = null): array;
}
