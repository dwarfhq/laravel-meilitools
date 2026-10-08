<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\Models\Movie;

/**
 * Test Helpers::guessModelNamespace() method.
 */
test('guess model namespace', function (): void {
    expect(Helpers::guessModelNamespace(Movie::class))->toBe(Movie::class)
        ->and(Helpers::guessModelNamespace('Movie'))->toBe(Movie::class)
        ->and(Helpers::guessModelNamespace('Fake'))->toBe('Fake')
    ;
});

/**
 * Test Helpers::scoutIndexSettings() method.
 */
test('scout index settings', function (): void {
    expect(Helpers::scoutIndexSettings())->toBeEmpty();

    config(['scout.meilisearch.index-settings' => [
        Movie::class => ['sortableAttributes' => ['rating']],
        MeiliMovie::class,
        'books'           => ['filterableAttributes' => ['author']],
        'testing-authors' => [],
    ]]);

    expect(Helpers::scoutIndexSettings())->toBe([
        Movie::class      => ['sortableAttributes' => ['rating']],
        MeiliMovie::class => [],
        'books'           => ['filterableAttributes' => ['author']],
        'testing-authors' => [],
    ])
        ->and(Helpers::scoutModels())->toBe([Movie::class, MeiliMovie::class])
        ->and(Helpers::scoutIndexes())->toBe([
            'testing-books'   => ['filterableAttributes' => ['author']],
            'testing-authors' => [],
        ])
    ;
});

/**
 * Test Helpers::scoutIndexName() method.
 */
test('scout index name', function (): void {
    expect(Helpers::scoutIndexName('books'))->toBe('testing-books')
        ->and(Helpers::scoutIndexName('testing-books'))->toBe('testing-books')
    ;

    config(['scout.prefix' => '']);
    expect(Helpers::scoutIndexName('books'))->toBe('books');
});

/**
 * Test Helpers::isSearchableModel() method.
 */
test('is searchable model', function (string $class, bool $expected): void {
    expect(Helpers::isSearchableModel($class))->toBe($expected);
})->with([
    'movie'       => [Movie::class, true],
    'meili movie' => [MeiliMovie::class, true],
    'not a model' => [Helpers::class, false],
    'fake'        => ['Fake', false],
]);

/**
 * Test Helpers::sortSettings() method.
 */
test('sort settings', function (): void {
    $sorted = Helpers::sortSettings([
        'typoTolerance' => [
            'disableOnNumbers'    => true,
            'disableOnWords'      => ['b', 'a'],
            'minWordSizeForTypos' => ['twoTypos' => 9, 'oneTypo' => 5],
        ],
        'synonyms'             => ['b' => ['y', 'x'], 'a' => ['z']],
        'stopWords'            => ['b', '10', 'a', '9', 'a'],
        'searchableAttributes' => ['b', 'a', 'b'],
        'filterableAttributes' => ['b', 'a', 'b'],
        'faceting'             => ['sortFacetValuesBy' => ['b' => 'count'], 'maxValuesPerFacet' => 10],
        'localizedAttributes'  => [['locales' => ['jpn'], 'attributePatterns' => ['b', 'a']]],
    ]);

    expect($sorted)->toBe([
        'faceting'             => ['maxValuesPerFacet' => 10, 'sortFacetValuesBy' => ['*' => 'alpha', 'b' => 'count']],
        'filterableAttributes' => ['b', 'a', 'b'],
        'localizedAttributes'  => [['attributePatterns' => ['b', 'a'], 'locales' => ['jpn']]],
        'searchableAttributes' => ['b', 'a'],
        'stopWords'            => ['10', '9', 'a', 'b'],
        'synonyms'             => ['a' => ['z'], 'b' => ['y', 'x']],
        'typoTolerance'        => [
            'minWordSizeForTypos' => ['oneTypo' => 5, 'twoTypos' => 9],
            'disableOnWords'      => ['a', 'b'],
            'disableOnNumbers'    => true,
        ],
    ]);
});

/**
 * Test Helpers::modelIndexName() method.
 */
test('model index name', function (): void {
    expect(Helpers::modelIndexName(Movie::class))->toBe('testing-movies')
        ->and(fn () => Helpers::modelIndexName(Helpers::class))
        ->toThrow(MeiliToolsException::class, "Class 'Dwarf\\MeiliTools\\Helpers' is not a searchable model")
    ;
});
