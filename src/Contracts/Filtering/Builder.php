<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Filtering;

interface Builder
{
    /**
     * Get filter string.
     *
     * @return string
     */
    public function filter(): string;
}
