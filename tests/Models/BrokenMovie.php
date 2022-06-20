<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Models;

use Dwarf\MeiliTools\Contracts\Indexes\MeiliSettings;

class BrokenMovie extends Movie implements MeiliSettings
{
    /**
     * {@inheritdoc}
     */
    public function meiliSettings(): array
    {
        return ['distinctAttribute' => 42];
    }
}
