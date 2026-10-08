<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

use Illuminate\Database\Eloquent\Model;

/**
 * Resets model index.
 */
interface ResetsModel
{
    /**
     * Reset model index settings to defaults.
     *
     * @param class-string<Model> $class
     *
     * @return array<string, array{old: mixed, new: mixed}> Changes keyed by setting.
     */
    public function __invoke(string $class, bool $pretend = false): array;
}
