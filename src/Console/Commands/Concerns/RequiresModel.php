<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands\Concerns;

use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Database\Eloquent\Model;

trait RequiresModel
{
    /**
     * Get model class.
     *
     * @throws MeiliToolsException When the class isn't a searchable model.
     *
     * @return class-string<Model>
     */
    protected function getModel(): string
    {
        $model = (string) ($this->argument('model') ?? $this->ask('What is the model class?'));
        $model = Helpers::guessModelNamespace($model);

        throw_unless(
            Helpers::isSearchableModel($model),
            MeiliToolsException::class,
            \sprintf("Class '%s' is not a searchable model", $model),
        );

        /** @var class-string<Model> $model */
        return $model;
    }
}
