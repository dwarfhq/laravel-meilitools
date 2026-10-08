<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

use Illuminate\Database\Eloquent\Model;

/**
 * Lists models with MeiliSearch index settings.
 */
interface ListsModels
{
    /**
     * Get the models in the configured paths implementing settings, and the models configured in Scout.
     *
     * @return list<class-string<Model>>
     */
    public function __invoke(): array;
}
