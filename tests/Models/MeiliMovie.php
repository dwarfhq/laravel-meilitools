<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Models;

use Dwarf\MeiliTools\Contracts\Indexes\MeiliSettings;

class MeiliMovie extends Movie implements MeiliSettings
{
    /**
     * {@inheritdoc}
     */
    public static function meiliSettings(): array
    {
        return include __DIR__ . '/../datasets/movie_settings.php';
    }
}
