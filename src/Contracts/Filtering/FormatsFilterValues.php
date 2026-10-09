<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Filtering;

use InvalidArgumentException;

/**
 * Formats fields and values for MeiliSearch filter expressions.
 */
interface FormatsFilterValues
{
    /**
     * Format a field name.
     *
     * @throws InvalidArgumentException When the field is invalid.
     */
    public function field(mixed $field): string;

    /**
     * Format a value, e.g. quoting strings.
     *
     * @throws InvalidArgumentException When the value type is unsupported.
     */
    public function value(mixed $value): string;

    /**
     * Format a number, without exponent notation.
     *
     * @throws InvalidArgumentException When the number isn't finite.
     */
    public function number(float|int $number): string;

    /**
     * Format a latitude and longitude pair for geo filters.
     *
     * @param array<array-key, mixed> $point
     *
     * @throws InvalidArgumentException When the point isn't a latitude and longitude pair.
     */
    public function point(array $point): string;
}
