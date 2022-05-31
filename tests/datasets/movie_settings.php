<?php

return [
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
