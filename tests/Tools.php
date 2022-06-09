<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests;

/**
 * @internal
 */
class Tools
{
    /**
     * Get movie settings.
     *
     * @param bool $sorted Whether settings should be sorted.
     *
     * @return array
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
            // Certain settings are automatically sorted by MeiliSearch,
            // so we do it the same way to correctly verify data.
            $sorter = function (&$value, $key) {
                if (\is_array($value)) {
                    if (\in_array($key, ['filterableAttributes', 'stopWords', 'sortableAttributes'], true)) {
                        sort($value);
                    }
                    if ($key === 'synonyms') {
                        ksort($value);
                        array_walk($value, fn (&$list) => sort($list));
                    }
                }
            };
            ksort($settings);
            array_walk($settings, $sorter);
        }

        return $settings;
    }
}
