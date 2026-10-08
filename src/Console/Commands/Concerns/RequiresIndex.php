<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands\Concerns;

trait RequiresIndex
{
    /**
     * Get index name.
     */
    protected function getIndex(): string
    {
        return (string) ($this->argument('index') ?? $this->ask('What is the index name?'));
    }
}
