<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Fixtures;

use Dwarf\MeiliTools\Tests\Models\Movie;

/**
 * Movie using its own Scout builder.
 */
class PlainMovie extends Movie
{
    /**
     * Scout builder used when searching.
     *
     * @var class-string<PlainBuilder<self>>
     */
    protected static string $scoutBuilder = PlainBuilder::class;

    public function searchableAs(): string
    {
        return config('scout.prefix') . 'movies';
    }
}
