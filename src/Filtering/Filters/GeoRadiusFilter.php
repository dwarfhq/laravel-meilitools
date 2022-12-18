<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering\Filters;

use Dwarf\MeiliTools\Contracts\Filtering\Filters\GeoRadiusFilter as Filter;

class GeoRadiusFilter implements Filter
{
    /**
     * Constructor.
     *
     * @param float $lat      Latitude.
     * @param float $lng      Longitude.
     * @param int   $distance Distance in meters.
     */
    public function __construct(protected float $lat, protected float $lng, protected int $distance)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return sprintf('_geoRadius(%s, %s, %s)', $this->lat, $this->lng, $this->distance);
    }
}
