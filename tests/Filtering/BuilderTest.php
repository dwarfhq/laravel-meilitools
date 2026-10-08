<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Contracts\Filtering\FilterBuilder;
use Dwarf\MeiliTools\Contracts\Filtering\FormatsFilterValues;
use Dwarf\MeiliTools\Contracts\Filtering\SearchBuilder;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Filtering\DistanceUnit;
use Dwarf\MeiliTools\Filtering\FilterValueFormatter;
use Dwarf\MeiliTools\Filtering\SearchBuilder as DefaultSearchBuilder;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Illuminate\Support\Facades\Http;
use Laravel\Scout\Builder as ScoutBuilder;
use Laravel\Scout\EngineManager;
use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;

/**
 * Index the given movie documents with filterable and sortable attributes.
 *
 * @param list<array<string, mixed>> $documents
 */
function indexMovies(string $index, array $documents): void
{
    resolve(SynchronizesIndex::class)($index, [
        'filterableAttributes' => ['__soft_deleted', '_geo', '_geojson', 'genre', 'rank', 'released', 'tags', 'title'],
        'sortableAttributes'   => ['_geo', 'rank'],
    ]);

    $client = resolve(Client::class);
    $task = $client->index($index)->addDocuments($documents, 'id');
    $client->waitForTask($task['taskUid']);
}

/**
 * Get the ids of the hits from a raw search result.
 *
 * @return list<int>
 */
function hitIds(ScoutBuilder $builder): array
{
    return array_column($builder->raw()['hits'], 'id');
}

/**
 * Movie documents used for searching.
 *
 * @return list<array<string, mixed>>
 */
function movieDocuments(): array
{
    return [
        [
            'id'       => 1,
            'title'    => 'Batman',
            'genre'    => 'action',
            'rank'     => 5,
            'released' => 1704067200,
            'tags'     => ['dc'],
            '_geo'     => ['lat' => 55.67, 'lng' => 12.56],
            '_geojson' => ['type' => 'Point', 'coordinates' => [12.56, 55.67]],
        ],
        [
            'id'       => 2,
            'title'    => 'Superman',
            'genre'    => 'action',
            'rank'     => 3,
            'released' => 1672531200,
            'tags'     => [],
            '_geo'     => ['lat' => 40.71, 'lng' => -74.0],
            '_geojson' => ['type' => 'Point', 'coordinates' => [-74.0, 40.71]],
        ],
        [
            'id'       => 3,
            'title'    => 'Amélie',
            'genre'    => 'drama',
            'rank'     => null,
            'released' => 1735689600,
            'tags'     => ['paris'],
            '_geo'     => ['lat' => 48.85, 'lng' => 2.35],
            '_geojson' => ['type' => 'Point', 'coordinates' => [2.35, 48.85]],
        ],
        [
            'id'    => 4,
            'title' => 'Robin',
            'genre' => 'drama',
        ],
    ];
}

/**
 * Test the Scout builder resolved for searchable models.
 */
test('resolves builder', function (): void {
    expect(Movie::search())->toBeInstanceOf(DefaultSearchBuilder::class);

    config(['scout.driver' => 'collection']);
    resolve(EngineManager::class)->forgetDrivers();

    expect(Movie::search())->toBeInstanceOf(ScoutBuilder::class)->not->toBeInstanceOf(SearchBuilder::class);
});

/**
 * Test searching with every filter.
 */
test('filters', function (Closure $build, array $expected): void {
    $this->withIndex('testing-movies', function () use ($build, $expected): void {
        indexMovies('testing-movies', movieDocuments());

        $ids = hitIds($build(Movie::search()));
        sort($ids);

        expect($ids)->toBe($expected);
    });
})->with([
    'none' => [
        fn (SearchBuilder $b) => $b,
        [1, 2, 3, 4],
    ],
    'where shorthand' => [
        fn (SearchBuilder $b) => $b->where('genre', 'action'),
        [1, 2],
    ],
    'where operator' => [
        fn (SearchBuilder $b) => $b->where('rank', '>=', 4),
        [1],
    ],
    'where not equals' => [
        fn (SearchBuilder $b) => $b->where('genre', '!=', 'action'),
        [3, 4],
    ],
    'where null' => [
        fn (SearchBuilder $b) => $b->where('rank', null),
        [3],
    ],
    'where date' => [
        fn (SearchBuilder $b) => $b->where('released', '>=', new DateTimeImmutable('2024-01-01 00:00:00 UTC')),
        [1, 3],
    ],
    'or where' => [
        fn (SearchBuilder $b) => $b->where('rank', '>=', 4)->orWhere('title', 'Robin'),
        [1, 4],
    ],
    'nested' => [
        fn (SearchBuilder $b) => $b
            ->where('genre', 'drama')
            ->where(fn (FilterBuilder $f) => $f->whereNull('rank')->orWhereNotExists('rank')),
        [3, 4],
    ],
    'or nested' => [
        fn (SearchBuilder $b) => $b
            ->where('rank', 5)
            ->orWhere(fn (FilterBuilder $f) => $f->where('genre', 'drama')->where('title', 'Robin')),
        [1, 4],
    ],
    'where nested not' => [
        fn (SearchBuilder $b) => $b->whereNested(fn (FilterBuilder $f) => $f->where('genre', 'action'), 'and', true),
        [3, 4],
    ],
    'where not' => [
        fn (SearchBuilder $b) => $b->whereNot('genre', 'action'),
        [3, 4],
    ],
    'or where not' => [
        fn (SearchBuilder $b) => $b->where('title', 'Batman')->orWhereNot('genre', 'drama'),
        [1, 2],
    ],
    'raw' => [
        fn (SearchBuilder $b) => $b->whereRaw('rank = 5 OR title = Robin'),
        [1, 4],
    ],
    'or raw' => [
        fn (SearchBuilder $b) => $b->where('rank', 3)->orWhereRaw('title = Robin'),
        [2, 4],
    ],
    'in' => [
        fn (SearchBuilder $b) => $b->whereIn('title', ['Batman', 'Robin']),
        [1, 4],
    ],
    'or in' => [
        fn (SearchBuilder $b) => $b->where('rank', 3)->orWhereIn('title', ['Robin']),
        [2, 4],
    ],
    'not in' => [
        fn (SearchBuilder $b) => $b->whereNotIn('title', ['Batman', 'Robin']),
        [2, 3],
    ],
    'or not in' => [
        fn (SearchBuilder $b) => $b->where('title', 'Amélie')->orWhereNotIn('genre', ['drama']),
        [1, 2, 3],
    ],
    'between' => [
        fn (SearchBuilder $b) => $b->whereBetween('rank', [3, 4]),
        [2],
    ],
    'or between' => [
        fn (SearchBuilder $b) => $b->where('title', 'Robin')->orWhereBetween('rank', [5, 6]),
        [1, 4],
    ],
    'not between' => [
        fn (SearchBuilder $b) => $b->whereNotBetween('rank', [3, 5]),
        [3, 4],
    ],
    'or not between' => [
        fn (SearchBuilder $b) => $b->where('rank', 5)->orWhereNotBetween('rank', [1, 10]),
        [1, 3, 4],
    ],
    'null' => [
        fn (SearchBuilder $b) => $b->whereNull('rank'),
        [3],
    ],
    'or null' => [
        fn (SearchBuilder $b) => $b->where('rank', 5)->orWhereNull('rank'),
        [1, 3],
    ],
    'not null' => [
        fn (SearchBuilder $b) => $b->whereNotNull('rank'),
        [1, 2, 4],
    ],
    'or not null' => [
        fn (SearchBuilder $b) => $b->where('title', 'Amélie')->orWhereNotNull('rank'),
        [1, 2, 3, 4],
    ],
    'empty' => [
        fn (SearchBuilder $b) => $b->whereEmpty('tags'),
        [2],
    ],
    'or empty' => [
        fn (SearchBuilder $b) => $b->where('rank', 5)->orWhereEmpty('tags'),
        [1, 2],
    ],
    'not empty' => [
        fn (SearchBuilder $b) => $b->whereNotEmpty('tags'),
        [1, 3, 4],
    ],
    'or not empty' => [
        fn (SearchBuilder $b) => $b->where('rank', 3)->orWhereNotEmpty('tags'),
        [1, 2, 3, 4],
    ],
    'exists' => [
        fn (SearchBuilder $b) => $b->whereExists('rank'),
        [1, 2, 3],
    ],
    'or exists' => [
        fn (SearchBuilder $b) => $b->where('title', 'Robin')->orWhereExists('rank'),
        [1, 2, 3, 4],
    ],
    'not exists' => [
        fn (SearchBuilder $b) => $b->whereNotExists('rank'),
        [4],
    ],
    'or not exists' => [
        fn (SearchBuilder $b) => $b->where('rank', 5)->orWhereNotExists('rank'),
        [1, 4],
    ],
    'starts with' => [
        fn (SearchBuilder $b) => $b->whereStartsWith('title', 'Bat'),
        [1],
    ],
    'or starts with' => [
        fn (SearchBuilder $b) => $b->where('rank', 3)->orWhereStartsWith('title', 'Rob'),
        [2, 4],
    ],
    'not starts with' => [
        fn (SearchBuilder $b) => $b->whereNotStartsWith('title', 'Bat'),
        [2, 3, 4],
    ],
    'or not starts with' => [
        fn (SearchBuilder $b) => $b->where('title', 'Batman')->orWhereNotStartsWith('genre', 'd'),
        [1, 2],
    ],
    'geo radius' => [
        fn (SearchBuilder $b) => $b->whereGeoRadius(55.67, 12.56, 1000),
        [1],
    ],
    'geo radius kilometers' => [
        fn (SearchBuilder $b) => $b->whereGeoRadius(48.85, 2.35, 1100, DistanceUnit::Kilometers),
        [1, 3],
    ],
    'geo radius miles' => [
        fn (SearchBuilder $b) => $b->whereGeoRadius(55.67, 12.56, 1, DistanceUnit::Miles),
        [1],
    ],
    'geo radius feet' => [
        fn (SearchBuilder $b) => $b->whereGeoRadius(55.67, 12.56, 5000, DistanceUnit::Feet),
        [1],
    ],
    'or geo radius' => [
        fn (SearchBuilder $b) => $b->where('title', 'Robin')->orWhereGeoRadius(48.85, 2.35, 1000),
        [3, 4],
    ],
    'not geo radius' => [
        fn (SearchBuilder $b) => $b->whereNotGeoRadius(55.67, 12.56, 1000),
        [2, 3, 4],
    ],
    'or not geo radius' => [
        fn (SearchBuilder $b) => $b->where('rank', 5)->orWhereNotGeoRadius(48.85, 2.35, 1000),
        [1, 2, 4],
    ],
    'geo bounding box' => [
        fn (SearchBuilder $b) => $b->whereGeoBoundingBox([56, 13], [48, 2]),
        [1, 3],
    ],
    'or geo bounding box' => [
        fn (SearchBuilder $b) => $b->where('title', 'Robin')->orWhereGeoBoundingBox([41, -73], [40, -75]),
        [2, 4],
    ],
    'not geo bounding box' => [
        fn (SearchBuilder $b) => $b->whereNotGeoBoundingBox([56, 13], [48, 2]),
        [2, 4],
    ],
    'or not geo bounding box' => [
        fn (SearchBuilder $b) => $b->where('rank', 5)->orWhereNotGeoBoundingBox([56, 13], [48, 2]),
        [1, 2, 4],
    ],
    'geo polygon' => [
        fn (SearchBuilder $b) => $b->whereGeoPolygon([[56, 12], [56, 13], [55, 13], [55, 12]]),
        [1],
    ],
    'or geo polygon' => [
        fn (SearchBuilder $b) => $b->where('title', 'Robin')->orWhereGeoPolygon([[49, 2], [49, 3], [48, 3], [48, 2]]),
        [3, 4],
    ],
    'not geo polygon' => [
        fn (SearchBuilder $b) => $b->whereNotGeoPolygon([[56, 12], [56, 13], [55, 13], [55, 12]]),
        [2, 3, 4],
    ],
    'or not geo polygon' => [
        fn (SearchBuilder $b) => $b->where('rank', 5)->orWhereNotGeoPolygon([[49, 2], [49, 3], [48, 3], [48, 2]]),
        [1, 2, 4],
    ],
]);

/**
 * Test searching with `CONTAINS` filters, which require an experimental feature.
 */
test('contains filters', function (): void {
    $config = config('scout.meilisearch');
    $request = Http::withToken($config['key'])->baseUrl($config['host']);
    $enabled = $request->get('/experimental-features')->json('containsFilter');
    $request->patch('/experimental-features', ['containsFilter' => true])->throw();

    try {
        $this->withIndex('testing-movies', function (): void {
            indexMovies('testing-movies', movieDocuments());

            expect(hitIds(Movie::search()->whereContains('title', 'man')))->toEqualCanonicalizing([1, 2])
                ->and(hitIds(Movie::search()->whereNotContains('title', 'man')))->toEqualCanonicalizing([3, 4])
                ->and(hitIds(Movie::search()->where('rank', 3)->orWhereContains('title', 'obi')))
                ->toEqualCanonicalizing([2, 4])
                ->and(hitIds(Movie::search()->where('rank', 5)->orWhereNotContains('title', 'man')))
                ->toEqualCanonicalizing([1, 3, 4])
            ;
        });
    } finally {
        $request->patch('/experimental-features', ['containsFilter' => $enabled])->throw();
    }
});

/**
 * Test searching with filters on a soft deleting model.
 */
test('filters with soft deletes', function (): void {
    config(['scout.soft_delete' => true]);

    $this->withIndex('testing-meili_movies', function (): void {
        $documents = movieDocuments();
        foreach ($documents as $key => $document) {
            $documents[$key]['__soft_deleted'] = $document['id'] === 1 ? 1 : 0;
        }
        indexMovies('testing-meili_movies', $documents);

        $ids = hitIds(MeiliMovie::search()->where('genre', 'action'));
        expect($ids)->toBe([2])
            ->and(hitIds(MeiliMovie::search()->where('genre', 'action')->withTrashed()))->toEqualCanonicalizing([1, 2])
            ->and(hitIds(MeiliMovie::search()->where('genre', 'action')->onlyTrashed()))->toBe([1])
        ;
    });
});

/**
 * Test the search callback receiving the filter and search options.
 */
test('search callback', function (): void {
    $this->withIndex('testing-movies', function (): void {
        indexMovies('testing-movies', movieDocuments());

        $options = [];
        $builder = Movie::search('', function (Indexes $index, string $query, array $params) use (&$options): array {
            $options = $params;

            return $index->rawSearch($query, $params);
        });
        $builder->options(['filter' => 'rank EXISTS'])
            ->where('genre', 'action')
            ->orWhere('genre', 'drama')
            ->matchingStrategy('frequency')
            ->rankingScoreThreshold(0.5)
            ->attributesToSearchOn(['title'])
            ->distinct('genre')
            ->locales(['eng'])
        ;

        expect(hitIds($builder))->toHaveCount(2)
            ->and($options)->toMatchArray([
                'filter'                => '(rank EXISTS) AND (genre = "action" OR genre = "drama")',
                'matchingStrategy'      => 'frequency',
                'rankingScoreThreshold' => 0.5,
                'attributesToSearchOn'  => ['title'],
                'distinct'              => 'genre',
                'locales'               => ['eng'],
            ])
        ;
    });
});

/**
 * Test sorting by distance to a geo point.
 */
test('order by geo', function (): void {
    $this->withIndex('testing-movies', function (): void {
        indexMovies('testing-movies', movieDocuments());

        // Copenhagen, then Paris and New York, with documents without a location last.
        expect(hitIds(Movie::search()->orderByGeo(55.67, 12.56)))->toBe([1, 3, 2, 4])
            // MeiliSearch panics on a bounding box covering the whole globe when `_geojson` is filterable.
            ->and(hitIds(
                Movie::search()->whereGeoBoundingBox([89, 179], [-89, -179])->orderByGeo(55.67, 12.56, 'desc'),
            ))
            ->toBe([2, 3, 1])
        ;
    });
});

/**
 * Test search option validation.
 */
test('invalid options', function (Closure $build, string $message): void {
    expect(fn () => $build(Movie::search()))->toThrow(InvalidArgumentException::class, $message);
})->with([
    'matching strategy' => [fn (SearchBuilder $b) => $b->matchingStrategy('some'), 'Invalid matching strategy [some]'],
    'threshold'         => [
        fn (SearchBuilder $b) => $b->rankingScoreThreshold(1.5),
        'The ranking score threshold must be between 0 and 1',
    ],
]);

/**
 * Test replacing the search builder through its contract.
 */
test('custom search builder', function (): void {
    $builder = new class(new Movie(), '') extends DefaultSearchBuilder
    {
        public function whereReleased(): static
        {
            return $this->whereExists('released');
        }
    };
    app()->bind(SearchBuilder::class, $builder::class);

    expect(Movie::search()->whereReleased()->toFilter())->toBe('released EXISTS');
});

/**
 * Test replacing the search builder with a class not extending Scout's builder.
 */
test('invalid search builder', function (): void {
    app()->bind(SearchBuilder::class, Dwarf\MeiliTools\Filtering\FilterBuilder::class);

    Movie::search();
})->throws(MeiliToolsException::class, "must extend Scout's builder");

/**
 * Test replacing how values are formatted through the formatter contract.
 */
test('custom value formatter', function (): void {
    app()->bind(FormatsFilterValues::class, fn (): FormatsFilterValues => new class extends FilterValueFormatter
    {
        public function value(mixed $value): string
        {
            return $value instanceof DateTimeInterface ? parent::value($value->format('Y-m-d')) : parent::value($value);
        }
    });

    $builder = Movie::search();
    assert($builder instanceof SearchBuilder);

    $filter = $builder
        ->where('released', '>=', new DateTimeImmutable('2024-01-01'))
        ->where(fn (FilterBuilder $filter) => $filter->whereBetween('updated', [
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-12-31'),
        ]))
        ->toFilter()
    ;

    expect($filter)->toBe('released >= "2024-01-01" AND (updated "2024-01-01" TO "2024-12-31")');
});
