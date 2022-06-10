<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Models;

use Dwarf\MeiliTools\Contracts\Indexes\MeiliSettings;
use Dwarf\MeiliTools\Tests\Tools;

class MeiliMovie extends Movie implements MeiliSettings
{
    /**
     * {@inheritdoc}
     */
    public function meiliSettings(): array
    {
        return Tools::movieSettings();
    }
}
