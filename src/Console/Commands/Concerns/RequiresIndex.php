<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands\Concerns;

use Dwarf\MeiliTools\Exceptions\MeiliToolsException;

trait RequiresIndex
{
    /**
     * Get index name.
     *
     * @throws MeiliToolsException When no index name is given.
     */
    protected function getIndex(): string
    {
        $index = (string) ($this->argument('index') ?? $this->ask('What is the index name?'));

        throw_if($index === '', MeiliToolsException::class, 'An index name is required');

        return $index;
    }
}
