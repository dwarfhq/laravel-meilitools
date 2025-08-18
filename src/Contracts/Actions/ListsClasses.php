<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Lists classes.
 */
interface ListsClasses
{
    /**
     * List classes for a given path with the provided namespace.
     *
     * @param string        $path      Path to scan for models.
     * @param string        $namespace Namespace matching the models.
     * @param callable|null $filter    Optional callback filter.
     */
    public function __invoke(string $path, string $namespace, ?callable $filter = null): array;
}
