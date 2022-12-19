<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Filtering\Filters\BasicFilter;
use Dwarf\MeiliTools\Contracts\Filtering\Filters\BetweenFilter;
use Dwarf\MeiliTools\Contracts\Filtering\Filters\GeoRadiusFilter;
use Dwarf\MeiliTools\Contracts\Filtering\Filters\GroupFilter;

it('formats basic booleans', function (string $key, bool $value, string $operator, string $expected) {
    expect(app(BasicFilter::class, compact('key', 'value', 'operator')))->__toString()->toBe($expected);
})->with([
    ['foo', true, '=', 'foo=true'],
    ['foo', false, '=', 'foo=false'],
    ['foo', true, '!=', 'foo!=true'],
    ['foo', false, '!=', 'foo!=false'],
]);

it('formats basic integers', function (string $key, int $value, string $operator, string $expected) {
    expect(app(BasicFilter::class, compact('key', 'value', 'operator')))->__toString()->toBe($expected);
})->with([
    ['num', 42, '=', 'num=42'],
    ['num', 42, '!=', 'num!=42'],
    ['num', 42, '<', 'num<42'],
    ['num', 42, '<=', 'num<=42'],
    ['num', 42, '>', 'num>42'],
    ['num', 42, '>=', 'num>=42'],
]);

it('formats basic floats', function (string $key, float $value, string $operator, string $expected) {
    expect(app(BasicFilter::class, compact('key', 'value', 'operator')))->__toString()->toBe($expected);
})->with([
    ['num', 42.42, '=', 'num=42.42'],
    ['num', 42.42, '!=', 'num!=42.42'],
    ['num', 42.42, '<', 'num<42.42'],
    ['num', 42.42, '<=', 'num<=42.42'],
    ['num', 42.42, '>', 'num>42.42'],
    ['num', 42.42, '>=', 'num>=42.42'],
]);

it('formats basic strings', function (string $key, string $value, string $operator, string $expected) {
    expect(app(BasicFilter::class, compact('key', 'value', 'operator')))->__toString()->toBe($expected);
})->with([
    ['foo', 'bar', '=', 'foo="bar"'],
    ['foo', 'bar', '!=', 'foo!="bar"'],
]);

it('formats between numbers', function (string $key, $low, $high, string $expected) {
    expect(app(BetweenFilter::class, compact('key', 'low', 'high')))->__toString()->toBe($expected);
})->with([
    ['num', 24, 42, 'num 24 TO 42'],
    ['num', 24.24, 42.42, 'num 24.24 TO 42.42'],
]);

it('formats geo radius', function (float $lat, float $lng, int $distance, string $expected) {
    expect(app(GeoRadiusFilter::class, compact('lat', 'lng', 'distance')))->__toString()->toBe($expected);
})->with([
    [24.24, 42.42, 2, '_geoRadius(24.24, 42.42, 2)'],
    [-24.24, -42.42, 2, '_geoRadius(-24.24, -42.42, 2)'],
]);

it('formats groups', function (string $key, array $values, string $expected) {
    expect(app(GroupFilter::class, compact('key', 'values')))->__toString()->toBe($expected);
})->with([
    ['num', [24, 42], '(num=24 OR num=42)'],
    ['num', [24.24, 42.42], '(num=24.24 OR num=42.42)'],
    ['foo', ['bar', 'baz'], '(foo="bar" OR foo="baz")'],
]);
