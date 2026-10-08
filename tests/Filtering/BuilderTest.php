<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Filtering\Builder;
use Dwarf\MeiliTools\Filtering\FilterBuilder;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\Models\Movie;
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
        'filterableAttributes' => ['__soft_deleted', '_geo', 'genre', 'rank', 'released', 'tags', 'title'],
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
        ],
        [
            'id'       => 2,
            'title'    => 'Superman',
            'genre'    => 'action',
            'rank'     => 3,
            'released' => 1672531200,
            'tags'     => [],
            '_geo'     => ['lat' => 40.71, 'lng' => -74.0],
        ],
        [
            'id'       => 3,
            'title'    => 'Amélie',
            'genre'    => 'drama',
            'rank'     => null,
            'released' => 1735689600,
            'tags'     => ['paris'],
            '_geo'     => ['lat' => 48.85, 'lng' => 2.35],
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
    expect(Movie::search())->toBeInstanceOf(Builder::class);

    config(['scout.driver' => 'collection']);
    resolve(EngineManager::class)->forgetDrivers();

    expect(Movie::search())->toBeInstanceOf(ScoutBuilder::class)->not->toBeInstanceOf(Builder::class);
});

/**
 * Test searching with filters.
 */
test('filters', function (Closure $build, array $expected): void {
    $this->withIndex('testing-movies', function () use ($build, $expected): void {
        indexMovies('testing-movies', movieDocuments());

        $ids = hitIds($build(Movie::search()));
        sort($ids);

        expect($ids)->toBe($expected);
    });
})->with([
    'none'        => [fn (Builder $b) => $b, [1, 2, 3, 4]],
    'scout where' => [fn (Builder $b) => $b->where('genre', 'action'), [1, 2]],
    'operator'    => [fn (Builder $b) => $b->where('rank', '>=', 4), [1]],
    'or'          => [fn (Builder $b) => $b->where('rank', '>=', 4)->orWhere('title', 'Robin'), [1, 4]],
    'nested'      => [
        fn (Builder $b) => $b
            ->where('genre', 'drama')
            ->where(fn (FilterBuilder $f) => $f->whereNull('rank')->orWhereNotExists('rank')),
        [3, 4],
    ],
    'not'     => [fn (Builder $b) => $b->whereNot('genre', 'action'), [3, 4]],
    'in'      => [fn (Builder $b) => $b->whereIn('title', ['Batman', 'Robin']), [1, 4]],
    'not in'  => [fn (Builder $b) => $b->whereNotIn('title', ['Batman', 'Robin']), [2, 3]],
    'between' => [fn (Builder $b) => $b->whereBetween('rank', [3, 4]), [2]],
    'date'    => [
        fn (Builder $b) => $b->where('released', '>=', new DateTimeImmutable('2024-01-01 00:00:00 UTC')),
        [1, 3],
    ],
    'empty'            => [fn (Builder $b) => $b->whereEmpty('tags'), [2]],
    'exists'           => [fn (Builder $b) => $b->whereExists('rank'), [1, 2, 3]],
    'starts with'      => [fn (Builder $b) => $b->whereStartsWith('title', 'Bat'), [1]],
    'geo radius'       => [fn (Builder $b) => $b->whereGeoRadius(55.67, 12.56, 1000), [1]],
    'geo bounding box' => [fn (Builder $b) => $b->whereGeoBoundingBox([56, 13], [48, 2]), [1, 3]],
    'raw'              => [fn (Builder $b) => $b->whereRaw('rank = 5 OR title = Robin'), [1, 4]],
]);

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
            ->and(hitIds(
                Movie::search()->whereGeoBoundingBox([90, 180], [-90, -180])->orderByGeo(55.67, 12.56, 'desc'),
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
    'matching strategy' => [fn (Builder $b) => $b->matchingStrategy('some'), 'Invalid matching strategy [some]'],
    'threshold'         => [
        fn (Builder $b) => $b->rankingScoreThreshold(1.5),
        'The ranking score threshold must be between 0 and 1',
    ],
]);
