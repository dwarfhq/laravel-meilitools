<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering\Filters;

use Dwarf\MeiliTools\Contracts\Filtering\Filters\BetweenFilter as Filter;

class BetweenFilter implements Filter
{
    /**
     * Constructor.
     *
     * @param string    $key  Key.
     * @param float|int $low  Low.
     * @param float|int $high High.
     */
    public function __construct(protected string $key, protected $low, protected $high)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return sprintf('%s %s TO %s', $this->key, $this->low, $this->high);
    }
}
