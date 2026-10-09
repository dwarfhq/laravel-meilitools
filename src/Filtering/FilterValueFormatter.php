<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering;

use BackedEnum;
use DateTimeInterface;
use Dwarf\MeiliTools\Contracts\Filtering\FormatsFilterValues;
use InvalidArgumentException;
use Stringable;

/**
 * Formats fields and values for MeiliSearch filter expressions.
 */
class FilterValueFormatter implements FormatsFilterValues
{
    public function field(mixed $field): string
    {
        if (!\is_string($field) || $field === '') {
            throw new InvalidArgumentException('Filter fields must be non-empty strings');
        }

        return $field;
    }

    /**
     * {@inheritDoc}
     *
     * Dates are converted to Unix timestamps, so they must be indexed as timestamps to be filterable.
     */
    public function value(mixed $value): string
    {
        return match (true) {
            \is_bool($value)                    => $value ? 'true' : 'false',
            \is_int($value), \is_float($value)  => $this->number($value),
            \is_string($value)                  => '"' . addcslashes($value, '"\\') . '"',
            $value instanceof DateTimeInterface => (string) $value->getTimestamp(),
            $value instanceof BackedEnum        => $this->value($value->value),
            $value instanceof Stringable        => $this->value((string) $value),
            default                             => throw new InvalidArgumentException(
                \sprintf('Unsupported filter value of type [%s]', get_debug_type($value)),
            ),
        };
    }

    public function number(float|int $number): string
    {
        if (\is_int($number)) {
            return (string) $number;
        }

        if (!is_finite($number)) {
            throw new InvalidArgumentException('Filter numbers must be finite');
        }

        $formatted = (string) $number;

        return str_contains($formatted, 'E')
            ? rtrim(rtrim(\sprintf('%.20F', $number), '0'), '.')
            : $formatted;
    }

    public function point(array $point): string
    {
        $point = array_values($point);
        if (\count($point) !== 2 || !is_numeric($point[0]) || !is_numeric($point[1])) {
            throw new InvalidArgumentException('Geo points must contain a latitude and a longitude');
        }

        return \sprintf('[%s, %s]', $this->number(+$point[0]), $this->number(+$point[1]));
    }
}
