<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering;

use Dwarf\MeiliTools\Filtering\Concerns\BuildsFilters;
use Stringable;

/**
 * Builds a MeiliSearch filter expression, e.g. for a nested group of filters.
 */
class FilterBuilder implements Stringable
{
    use BuildsFilters;

    public function __toString(): string
    {
        return $this->toFilter();
    }
}
