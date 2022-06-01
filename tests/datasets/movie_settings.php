<?php

use MeiliSearch\MeiliSearch;

$settings = [
    'rankingRules' => [
        'words',
        'typo',
        'proximity',
        'attribute',
        'sort',
        'exactness',
        'release_date:desc',
        'rank:desc',
    ],
    'distinctAttribute'    => 'movie_id',
    'searchableAttributes' => [
        'title',
        'overview',
        'genres',
    ],
    'displayedAttributes' => [
        'title',
        'overview',
        'genres',
        'release_date',
    ],
    'filterableAttributes' => [
        'release_date',
        'rank',
    ],
    'stopWords' => [
        'the',
        'a',
        'an',
    ],
    'sortableAttributes' => [
        'title',
        'release_date',
    ],
    'synonyms' => [
        'wolverine' => ['xmen', 'logan'],
        'logan'     => ['wolverine'],
    ],
];

// Add typo tolerance to default settings for version >=0.23.2.
if (version_compare(MeiliSearch::VERSION, '0.23.2', '>=')) {
    $settings['typoTolerance'] = [];
}

return $settings;
