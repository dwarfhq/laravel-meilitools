<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands\Concerns;

trait ChecksSynchronization
{
    /**
     * Whether changes are only shown, which is implied when checking.
     */
    protected function pretending(): bool
    {
        return (bool) $this->option('pretend') || $this->checking();
    }

    /**
     * Whether the command should fail when settings are out of sync.
     */
    protected function checking(): bool
    {
        return (bool) $this->option('check');
    }
}
