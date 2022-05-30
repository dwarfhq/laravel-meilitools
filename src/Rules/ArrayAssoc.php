<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Rules;

use Dwarf\MeiliTools\Contracts\Rules\ArrayAssocRule;
use Illuminate\Support\Arr;

class ArrayAssoc implements ArrayAssocRule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return Arr::isAssoc($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be an associative array.';
    }
}
