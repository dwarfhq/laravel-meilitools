<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Filtering;

interface FilterBuilder
{
    /**
     * Get filter string.
     *
     * @return string
     */
    public function __toString(): string;
}
