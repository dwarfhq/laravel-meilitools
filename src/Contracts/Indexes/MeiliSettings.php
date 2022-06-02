<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Indexes;

/**
 * MeiliSearch Index Settings.
 */
interface MeiliSettings
{
    /**
     * Index settings are represented as a JSON object literal,
     * containing a field for each possible customization option.
     *
     * @see https://docs.meilisearch.com/learn/configuration/settings.html
     *
     * Example settings:
     * <code>
     * return [
     *     'rankingRules' => [
     *         'words',
     *         'typo',
     *         'proximity',
     *         'attribute',
     *         'sort',
     *         'exactness',
     *         'release_date:desc',
     *         'rank:desc',
     *     ],
     *     'distinctAttribute' => 'movie_id',
     *     'searchableAttributes' => [
     *         'title',
     *         'overview',
     *         'genres',
     *     ],
     *     'displayedAttributes' => [
     *         'title',
     *         'overview',
     *         'genres',
     *         'release_date',
     *     ],
     *     'filterableAttributes' => [
     *         'release_date',
     *         'rank',
     *     ],
     *     'stopWords' => [
     *         'the',
     *         'a',
     *         'an',
     *     ],
     *     'sortableAttributes' => [
     *         'title',
     *         'release_date',
     *     ],
     *     'synonyms' => [
     *         'wolverine' => ['xmen', 'logan'],
     *         'logan'     => ['wolverine'],
     *     ],
     * ];
     * </code>
     *
     * @return array
     */
    public static function meiliSettings(): array;
}
