<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands\Concerns;

trait RequiresModel
{
    /**
     * Get model class.
     *
     * @return string
     */
    protected function getModel(): string
    {
        return $this->argument('model') ?? $this->ask('What is the model class?');
    }
}
