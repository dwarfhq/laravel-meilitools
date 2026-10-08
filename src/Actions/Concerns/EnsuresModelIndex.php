<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions\Concerns;

use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Illuminate\Database\Eloquent\Model;

/**
 * @property EnsuresIndexExists $ensureIndexExists
 */
trait EnsuresModelIndex
{
    /**
     * Ensure the model index exists and get its name.
     *
     * @param class-string<Model> $class
     *
     * @throws MeiliToolsException When the class isn't a searchable model.
     */
    protected function ensureModelIndex(string $class): string
    {
        $model = resolve($class);
        if (
            !$model instanceof Model
            || !method_exists($model, 'searchableAs')
            || !method_exists($model, 'getScoutKeyName')
        ) {
            throw new MeiliToolsException(\sprintf("Class '%s' is not a searchable model", $class));
        }

        $index = $model->searchableAs();

        ($this->ensureIndexExists)($index, ['primaryKey' => $model->getScoutKeyName()]);

        return $index;
    }
}
