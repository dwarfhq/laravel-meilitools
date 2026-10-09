<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering;

use Dwarf\MeiliTools\Filtering\Concerns\AppliesSearchBuilder;
use Laravel\Scout\Engines\MeilisearchEngine as ScoutMeilisearchEngine;

/**
 * Scout MeiliSearch engine applying the filters and search parameters of the search builder.
 */
class MeilisearchEngine extends ScoutMeilisearchEngine
{
    use AppliesSearchBuilder;
}
