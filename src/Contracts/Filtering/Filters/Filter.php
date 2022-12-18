<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Filtering\Filters;

interface Filter
{
    /**
     * Get filter string.
     *
     * @return string
     */
    public function __toString(): string;
}
