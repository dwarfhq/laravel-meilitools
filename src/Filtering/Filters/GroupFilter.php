<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering\Filters;

use Dwarf\MeiliTools\Contracts\Filtering\Filters\GroupFilter as Filter;

class GroupFilter implements Filter
{
    /**
     * Constructor.
     *
     * @param string   $key    Key.
     * @param iterable $values Values.
     */
    public function __construct(protected string $key, protected iterable $values)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return sprintf('(%s)', collect($this->values)->map(function ($value) {
            return (string) new BasicFilter($this->key, $value);
        })->implode(' OR '));
    }
}
