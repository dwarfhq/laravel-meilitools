<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Rules;

use Closure;
use Dwarf\MeiliTools\Contracts\Rules\ArrayAssocRule;
use Illuminate\Support\Arr;

class ArrayAssoc implements ArrayAssocRule
{
    /**
     * Validation error message.
     */
    public const string MESSAGE = 'The :attribute must be an associative array.';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!\is_array($value) || ($value !== [] && !Arr::isAssoc($value))) {
            $fail(self::MESSAGE);
        }
    }
}
