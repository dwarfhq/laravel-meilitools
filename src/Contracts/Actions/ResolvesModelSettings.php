<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

use Illuminate\Database\Eloquent\Model;

/**
 * Resolves model index settings.
 */
interface ResolvesModelSettings
{
    /**
     * Resolve index settings for a model.
     *
     * Combines settings from Scout's MeiliSearch index settings configuration
     * with settings from the model's `meiliSettings()` method.
     *
     * @param class-string<Model> $class
     *
     * @return array<string, mixed>
     */
    public function __invoke(string $class): array;
}
