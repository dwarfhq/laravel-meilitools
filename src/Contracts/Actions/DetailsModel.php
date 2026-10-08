<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

use Illuminate\Database\Eloquent\Model;

/**
 * Details model index.
 */
interface DetailsModel
{
    /**
     * Get model index settings.
     *
     * @param class-string<Model> $class
     *
     * @return array<string, mixed>
     */
    public function __invoke(string $class): array;
}
