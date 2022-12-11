<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Models;

use Dwarf\MeiliTools\Contracts\Indexes\MeiliSettings;
use Dwarf\MeiliTools\Tests\Tools;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeiliMovie extends Movie implements MeiliSettings
{
    use SoftDeletes;

    /**
     * {@inheritdoc}
     */
    public function meiliSettings(): array
    {
        return Tools::movieSettings();
    }
}
