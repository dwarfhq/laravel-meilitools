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
     * @return array<string, mixed>
     */
    public static function movieSettings(bool $sorted = true): array
    {
        $settings = [
            'rankingRules' => [
                'words',
                'typo',
                'proximity',
                'attributeRank',
                'sort',
                'wordPosition',
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
            return Helpers::sortSettings($settings);
        }

        return $settings;
    }

    /**
     * Get advanced settings, covering settings beyond the basic movie settings.
     *
     * @return array<string, mixed>
     */
    public static function advancedSettings(bool $sorted = true): array
    {
        $settings = [
            'dictionary'  => ['J. R. R.', 'W. E. B.'],
            'facetSearch' => false,
            'faceting'    => [
                'maxValuesPerFacet' => 50,
                'sortFacetValuesBy' => ['*' => 'alpha', 'genres' => 'count'],
            ],
            'localizedAttributes' => [
                ['attributePatterns' => ['*_ja'], 'locales' => ['jpn']],
                ['attributePatterns' => ['title', 'overview'], 'locales' => []],
            ],
            'nonSeparatorTokens' => ['@', '#'],
            'pagination'         => ['maxTotalHits' => 500],
            'prefixSearch'       => 'disabled',
            'proximityPrecision' => 'byAttribute',
            'searchCutoffMs'     => 150,
            'separatorTokens'    => ['|', '&hellip;'],
            'typoTolerance'      => [
                'enabled'             => false,
                'minWordSizeForTypos' => ['oneTypo' => 4, 'twoTypos' => 8],
                'disableOnWords'      => ['xmen'],
                'disableOnAttributes' => ['genres'],
                'disableOnNumbers'    => true,
            ],
        ];

        if ($sorted) {
            return Helpers::sortSettings($settings);
        }

        return $settings;
    }

    /**
     * Get the expected changes between old and new settings.
     *
     * @param array<string, mixed> $old
     * @param array<string, mixed> $new
     *
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public static function changes(array $old, array $new): array
    {
        $changes = [];
        foreach ($new as $key => $value) {
            if (($old[$key] ?? null) !== $value) {
                $changes[$key] = ['old' => $old[$key] ?? null, 'new' => $value];
            }
        }

        return $changes;
    }
}
