<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Fixtures;

use Dwarf\MeiliTools\Tests\Models\Movie;

/**
 * Movie with an invalid document id, which MeiliSearch fails to index.
 */
class InvalidMovie extends Movie
{
    protected $table = 'movies';

    public function searchableAs(): string
    {
        return config('scout.prefix') . 'invalid_movies';
    }

    public function getScoutKey(): string
    {
        return 'invalid id ' . $this->getKey();
    }
}
