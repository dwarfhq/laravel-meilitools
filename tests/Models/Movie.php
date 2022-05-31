<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Movie extends Model
{
    use Searchable;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'name',
        'description',
        'keywords',
        'rating',
    ];
}
