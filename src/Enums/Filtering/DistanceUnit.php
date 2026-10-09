<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Enums\Filtering;

/**
 * Units of distance for geo filters.
 */
enum DistanceUnit: string
{
    case Meters = 'm';
    case Kilometers = 'km';
    case Miles = 'mi';
    case Feet = 'ft';

    /**
     * Convert a distance in this unit to meters, as used by MeiliSearch.
     */
    public function toMeters(float|int $distance): float|int
    {
        return match ($this) {
            self::Meters     => $distance,
            self::Kilometers => $distance * 1000,
            self::Miles      => $distance * 1609.344,
            self::Feet       => $distance * 0.3048,
        };
    }
}
