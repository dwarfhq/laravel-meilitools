<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools;

class Constants
{
    /**
     * Default MeiliSearch index settings.
     *
     * @var array
     */
    public const DEFAULT_SETTINGS = [
        'displayedAttributes'  => ['*'],
        'distinctAttribute'    => null,
        'filterableAttributes' => [],
        'rankingRules'         => ['words', 'typo', 'proximity', 'attribute', 'sort', 'exactness'],
        'searchableAttributes' => ['*'],
        'sortableAttributes'   => [],
        'stopWords'            => [],
        'synonyms'             => [],
    ];
}
