<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands\Concerns;

trait RequiresIndex
{
    /**
     * Get index name.
     *
     * @return string
     */
    protected function getIndex(): string
    {
        return $this->argument('index') ?? $this->ask('What is the index name?');
    }
}
