<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

use Illuminate\Database\Eloquent\Model;

/**
 * Views model index.
 */
interface ViewsModel
{
    /**
     * Get model index information.
     *
     * @param class-string<Model> $class
     *
     * @return array<string, mixed>
     */
    public function __invoke(string $class, bool $stats = false): array;
}
