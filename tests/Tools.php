<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests;

use Dwarf\MeiliTools\Helpers;

/**
 * @internal
 */
class Tools
{
    /**
     * Get movie settings.
     *
     * @param bool $sorted Whether settings should be sorted.
     */
    public static function movieSettings(bool $sorted = true): array
    {
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

        if ($sorted) {
            $settings = Helpers::sortSettings($settings);
        }

        return $settings;
    }
}
