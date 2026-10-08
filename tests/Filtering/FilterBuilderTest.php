<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Dwarf\MeiliTools\Filtering\FilterBuilder;
use Dwarf\MeiliTools\Tests\Fixtures\Genre;

/**
 * Test FilterBuilder comparisons and value formatting.
 */
test('where', function (Closure $build, string $expected): void {
    expect($build(new FilterBuilder())->toFilter())->toBe($expected);
})->with([
    'equals shorthand' => [fn (FilterBuilder $f) => $f->where('rank', 42), 'rank = 42'],
    'operator'         => [fn (FilterBuilder $f) => $f->where('rank', '>=', 4.5), 'rank >= 4.5'],
    'not equals alias' => [fn (FilterBuilder $f) => $f->where('rank', '<>', 1), 'rank != 1'],
    'true'             => [fn (FilterBuilder $f) => $f->where('active', true), 'active = true'],
    'false'            => [fn (FilterBuilder $f) => $f->where('active', false), 'active = false'],
    'string'           => [fn (FilterBuilder $f) => $f->where('title', 'Batman'), 'title = "Batman"'],
    'escaped string'   => [
        fn (FilterBuilder $f) => $f->where('title', 'Say "hi" \\o/'),
        'title = "Say \\"hi\\" \\\\o/"',
    ],
    'numeric string'  => [fn (FilterBuilder $f) => $f->where('code', '42'), 'code = "42"'],
    'negative number' => [fn (FilterBuilder $f) => $f->where('rank', '>', -1), 'rank > -1'],
    'large float'     => [
        fn (FilterBuilder $f) => $f->where('rank', '<', 1.0e25),
        'rank < 10000000000000000905969664',
    ],
    'backed enum' => [fn (FilterBuilder $f) => $f->where('genre', Genre::Action), 'genre = "action"'],
    'date'        => [
        fn (FilterBuilder $f) => $f->where('released', '>', CarbonImmutable::parse('2024-01-01 00:00:00 UTC')),
        'released > 1704067200',
    ],
    'null'         => [fn (FilterBuilder $f) => $f->where('rank', null), 'rank IS NULL'],
    'not null'     => [fn (FilterBuilder $f) => $f->where('rank', '!=', null), 'rank IS NOT NULL'],
    'and'          => [fn (FilterBuilder $f) => $f->where('a', 1)->where('b', 2), 'a = 1 AND b = 2'],
    'or'           => [fn (FilterBuilder $f) => $f->where('a', 1)->orWhere('b', '>', 2), 'a = 1 OR b > 2'],
    'or shorthand' => [fn (FilterBuilder $f) => $f->where('a', 1)->orWhere('b', 2), 'a = 1 OR b = 2'],
    'nested'       => [
        fn (FilterBuilder $f) => $f->where('a', 1)->where(fn (FilterBuilder $q) => $q->where('b', 2)->orWhere('c', 3)),
        'a = 1 AND (b = 2 OR c = 3)',
    ],
    'or nested' => [
        fn (FilterBuilder $f) => $f->where('a', 1)->orWhere(fn (FilterBuilder $q) => $q->where('b', 2)->where('c', 3)),
        'a = 1 OR (b = 2 AND c = 3)',
    ],
    'empty nested' => [fn (FilterBuilder $f) => $f->where('a', 1)->where(fn (FilterBuilder $q) => $q), 'a = 1'],
    'not'          => [fn (FilterBuilder $f) => $f->whereNot('a', 1), 'NOT (a = 1)'],
    'not operator' => [fn (FilterBuilder $f) => $f->whereNot('a', '>', 1), 'NOT (a > 1)'],
    'not nested'   => [
        fn (FilterBuilder $f) => $f->whereNot(fn (FilterBuilder $q) => $q->where('a', 1)->orWhere('b', 2)),
        'NOT (a = 1 OR b = 2)',
    ],
    'or not' => [fn (FilterBuilder $f) => $f->where('a', 1)->orWhereNot('b', 2), 'a = 1 OR NOT (b = 2)'],
    'raw'    => [
        fn (FilterBuilder $f) => $f->where('a', 1)->orWhereRaw('b = 2 AND c = 3'),
        'a = 1 OR (b = 2 AND c = 3)',
    ],
]);

/**
 * Test FilterBuilder operator filters.
 */
test('operators', function (Closure $build, string $expected): void {
    expect((string) $build(new FilterBuilder()))->toBe($expected);
})->with([
    'in' => [
        fn (FilterBuilder $f) => $f->whereIn('genre', ['action', Genre::Drama, 3]),
        'genre IN ["action", "drama", 3]',
    ],
    'in collection' => [fn (FilterBuilder $f) => $f->whereIn('rank', collect([1, 2])), 'rank IN [1, 2]'],
    'in empty'      => [fn (FilterBuilder $f) => $f->whereIn('rank', []), 'rank IN []'],
    'not in'        => [fn (FilterBuilder $f) => $f->whereNotIn('rank', [1, 2]), 'rank NOT IN [1, 2]'],
    'or in'         => [fn (FilterBuilder $f) => $f->where('a', 1)->orWhereIn('b', [2]), 'a = 1 OR b IN [2]'],
    'or not in'     => [
        fn (FilterBuilder $f) => $f->where('a', 1)->orWhereNotIn('b', [2]),
        'a = 1 OR b NOT IN [2]',
    ],
    'between'         => [fn (FilterBuilder $f) => $f->whereBetween('rank', [1, 5]), 'rank 1 TO 5'],
    'between strings' => [
        fn (FilterBuilder $f) => $f->whereBetween('date', ['2024-01', '2024-06']),
        'date "2024-01" TO "2024-06"',
    ],
    'not between' => [fn (FilterBuilder $f) => $f->whereNotBetween('rank', [1, 5]), 'NOT rank 1 TO 5'],
    'or between'  => [
        fn (FilterBuilder $f) => $f->where('a', 1)->orWhereBetween('b', [1, 2]),
        'a = 1 OR b 1 TO 2',
    ],
    'or not between' => [
        fn (FilterBuilder $f) => $f->where('a', 1)->orWhereNotBetween('b', [1, 2]),
        'a = 1 OR NOT b 1 TO 2',
    ],
    'null' => [
        fn (FilterBuilder $f) => $f->whereNull('rank')->orWhereNull('score'),
        'rank IS NULL OR score IS NULL',
    ],
    'not null' => [
        fn (FilterBuilder $f) => $f->whereNotNull('rank')->orWhereNotNull('score'),
        'rank IS NOT NULL OR score IS NOT NULL',
    ],
    'empty' => [
        fn (FilterBuilder $f) => $f->whereEmpty('tags')->orWhereEmpty('title'),
        'tags IS EMPTY OR title IS EMPTY',
    ],
    'not empty' => [
        fn (FilterBuilder $f) => $f->whereNotEmpty('tags')->orWhereNotEmpty('title'),
        'tags IS NOT EMPTY OR title IS NOT EMPTY',
    ],
    'exists' => [
        fn (FilterBuilder $f) => $f->whereExists('rank')->orWhereExists('score'),
        'rank EXISTS OR score EXISTS',
    ],
    'not exists' => [
        fn (FilterBuilder $f) => $f->whereNotExists('rank')->orWhereNotExists('score'),
        'rank NOT EXISTS OR score NOT EXISTS',
    ],
    'starts with' => [
        fn (FilterBuilder $f) => $f->whereStartsWith('title', 'Bat')->orWhereStartsWith('title', 'Sup'),
        'title STARTS WITH "Bat" OR title STARTS WITH "Sup"',
    ],
    'not starts with' => [
        fn (FilterBuilder $f) => $f->whereNotStartsWith('title', 'Bat')->orWhereNotStartsWith('title', 'Sup'),
        'title NOT STARTS WITH "Bat" OR title NOT STARTS WITH "Sup"',
    ],
    'contains' => [
        fn (FilterBuilder $f) => $f->whereContains('title', 'man')->orWhereContains('title', 'bat'),
        'title CONTAINS "man" OR title CONTAINS "bat"',
    ],
    'not contains' => [
        fn (FilterBuilder $f) => $f->whereNotContains('title', 'man')->orWhereNotContains('title', 'bat'),
        'title NOT CONTAINS "man" OR title NOT CONTAINS "bat"',
    ],
    'geo radius' => [
        fn (FilterBuilder $f) => $f->whereGeoRadius(55.67, 12.56, 1000),
        '_geoRadius(55.67, 12.56, 1000)',
    ],
    'geo radius resolution' => [
        fn (FilterBuilder $f) => $f->whereGeoRadius(-55.67, -12.5, 1000, 10),
        '_geoRadius(-55.67, -12.5, 1000, 10)',
    ],
    'not geo radius' => [
        fn (FilterBuilder $f) => $f->whereNotGeoRadius(55.67, 12.56, 1000),
        'NOT _geoRadius(55.67, 12.56, 1000)',
    ],
    'or geo radius' => [
        fn (FilterBuilder $f) => $f->where('a', 1)->orWhereGeoRadius(1, 2, 3)->orWhereNotGeoRadius(4, 5, 6),
        'a = 1 OR _geoRadius(1, 2, 3) OR NOT _geoRadius(4, 5, 6)',
    ],
    'geo bounding box' => [
        fn (FilterBuilder $f) => $f->whereGeoBoundingBox([56, 13.5], [55, 12]),
        '_geoBoundingBox([56, 13.5], [55, 12])',
    ],
    'not geo bounding box' => [
        fn (FilterBuilder $f) => $f->whereNotGeoBoundingBox([56, 13], [55, 12]),
        'NOT _geoBoundingBox([56, 13], [55, 12])',
    ],
    'or geo bounding box' => [
        fn (FilterBuilder $f) => $f
            ->where('a', 1)
            ->orWhereGeoBoundingBox([2, 2], [1, 1])
            ->orWhereNotGeoBoundingBox([4, 4], [3, 3]),
        'a = 1 OR _geoBoundingBox([2, 2], [1, 1]) OR NOT _geoBoundingBox([4, 4], [3, 3])',
    ],
    'geo polygon' => [
        fn (FilterBuilder $f) => $f->whereGeoPolygon([[56, 12], [56, 13], [55, 13]]),
        '_geoPolygon([56, 12], [56, 13], [55, 13])',
    ],
    'not geo polygon' => [
        fn (FilterBuilder $f) => $f->whereNotGeoPolygon([[1, 1], [1, 2], [2, 2]]),
        'NOT _geoPolygon([1, 1], [1, 2], [2, 2])',
    ],
    'or geo polygon' => [
        fn (FilterBuilder $f) => $f
            ->where('a', 1)
            ->orWhereGeoPolygon([[1, 1], [1, 2], [2, 2]])
            ->orWhereNotGeoPolygon([[3, 3], [3, 4], [4, 4]]),
        'a = 1 OR _geoPolygon([1, 1], [1, 2], [2, 2]) OR NOT _geoPolygon([3, 3], [3, 4], [4, 4])',
    ],
]);

/**
 * Test FilterBuilder argument validation.
 */
test('invalid arguments', function (Closure $build, string $message): void {
    expect(fn () => $build(new FilterBuilder()))->toThrow(InvalidArgumentException::class, $message);
})->with([
    'operator'      => [fn (FilterBuilder $f) => $f->where('a', 'LIKE', 'b'), 'Invalid filter operator [LIKE]'],
    'null operator' => [fn (FilterBuilder $f) => $f->where('a', '>', null), 'Invalid filter operator [>] for null'],
    'boolean'       => [fn (FilterBuilder $f) => $f->where('a', '=', 1, 'xor'), 'Invalid filter boolean [XOR]'],
    'field'         => [fn (FilterBuilder $f) => $f->where('', 1), 'Filter fields must be non-empty strings'],
    'value'         => [fn (FilterBuilder $f) => $f->where('a', [1]), 'Unsupported filter value of type [array]'],
    'infinite'      => [fn (FilterBuilder $f) => $f->where('a', INF), 'Filter numbers must be finite'],
    'between'       => [
        fn (FilterBuilder $f) => $f->whereBetween('a', [1]),
        'A between filter requires exactly two values',
    ],
    'polygon' => [
        fn (FilterBuilder $f) => $f->whereGeoPolygon([[1, 1], [2, 2]]),
        'A geo polygon filter requires at least three points',
    ],
    'point' => [
        fn (FilterBuilder $f) => $f->whereGeoBoundingBox([1], [2, 2]),
        'Geo points must contain a latitude and a longitude',
    ],
]);
