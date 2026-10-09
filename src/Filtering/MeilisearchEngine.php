<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering;

use Dwarf\MeiliTools\Contracts\Engines\ImportsIntoIndex as ImportsIntoIndexContract;
use Dwarf\MeiliTools\Filtering\Concerns\AppliesSearchBuilder;
use Dwarf\MeiliTools\Filtering\Concerns\ImportsIntoIndex;
use Laravel\Scout\Engines\MeilisearchEngine as ScoutMeilisearchEngine;

/**
 * Scout MeiliSearch engine applying the search builder, and importing models into other indexes.
 */
class MeilisearchEngine extends ScoutMeilisearchEngine implements ImportsIntoIndexContract
{
    use AppliesSearchBuilder;
    use ImportsIntoIndex;
}
