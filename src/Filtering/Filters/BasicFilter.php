<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering\Filters;

use Dwarf\MeiliTools\Contracts\Filtering\Filters\BasicFilter as Filter;

class BasicFilter implements Filter
{
    /**
     * Constructor.
     *
     * @param string $key      Key.
     * @param mixed  $value    Value.
     * @param string $operator Operator.
     */
    public function __construct(protected string $key, protected $value, protected string $operator = '=')
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        if (is_bool($this->value)) {
            return sprintf('%s%s%s', $this->key, $this->operator, $this->value ? 'true' : 'false');
        }

        return is_numeric($this->value)
            ? sprintf('%s%s%s', $this->key, $this->operator, $this->value)
            : sprintf('%s%s"%s"', $this->key, $this->operator, $this->value);
    }
}
