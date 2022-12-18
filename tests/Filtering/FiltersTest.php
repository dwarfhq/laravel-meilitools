<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Filtering\Filters\BasicFilter;
use Dwarf\MeiliTools\Contracts\Filtering\Filters\BetweenFilter;
use Dwarf\MeiliTools\Contracts\Filtering\Filters\GeoRadiusFilter;
use Dwarf\MeiliTools\Contracts\Filtering\Filters\GroupFilter;

it('formats basic booleans', function (string $key, bool $value, ?string $operator, string $expected) {
    expect(app(BasicFilter::class, compact('key', 'value', 'operator')))->__toString()->toBe($expected);
})->with([
    ['foo', true, '=', 'foo=true'],
    ['foo', false, '=', 'foo=false'],
    ['foo', true, '!=', 'foo!=true'],
    ['foo', false, '!=', 'foo!=false'],
]);

it('formats basic integers', function (string $key, int $value, ?string $operator, string $expected) {
    expect(app(BasicFilter::class, compact('key', 'value', 'operator')))->__toString()->toBe($expected);
})->with([
    ['num', 42, '=', 'num=42'],
    ['num', 42, '!=', 'num!=42'],
    ['num', 42, '<', 'num<42'],
    ['num', 42, '<=', 'num<=42'],
    ['num', 42, '>', 'num>42'],
    ['num', 42, '>=', 'num>=42'],
]);

it('formats basic floats', function (string $key, float $value, ?string $operator, string $expected) {
    expect(app(BasicFilter::class, compact('key', 'value', 'operator')))->__toString()->toBe($expected);
})->with([
    ['num', 42.42, '=', 'num=42.42'],
    ['num', 42.42, '!=', 'num!=42.42'],
    ['num', 42.42, '<', 'num<42.42'],
    ['num', 42.42, '<=', 'num<=42.42'],
    ['num', 42.42, '>', 'num>42.42'],
    ['num', 42.42, '>=', 'num>=42.42'],
]);

it('formats basic strings', function (string $key, string $value, ?string $operator, string $expected) {
    expect(app(BasicFilter::class, compact('key', 'value', 'operator')))->__toString()->toBe($expected);
})->with([
    ['foo', 'bar', '=', 'foo="bar"'],
    ['foo', 'bar', '!=', 'foo!="bar"'],
]);

it('formats between numbers', function () {
    expect(app(BetweenFilter::class, ['key' => 'num', 'low' => 24, 'high' => 42]))
        ->__toString()->toBe('num 24 TO 42');
    expect(app(BetweenFilter::class, ['key' => 'num', 'low' => 24.24, 'high' => 42.42]))
        ->__toString()->toBe('num 24.24 TO 42.42');
});

it('formats geo radius', function () {
    expect(app(GeoRadiusFilter::class, ['lat' => 24.24, 'lng' => 42.42, 'distance' => 2]))
        ->__toString()->toBe('_geoRadius(24.24, 42.42, 2)');
    expect(app(GeoRadiusFilter::class, ['lat' => -24.24, 'lng' => -42.42, 'distance' => 2]))
        ->__toString()->toBe('_geoRadius(-24.24, -42.42, 2)');
});

it('formats groups', function () {
    expect(app(GroupFilter::class, ['key' => 'num', 'values' => [24, 42]]))
        ->__toString()->toBe('(num=24 OR num=42)');
    expect(app(GroupFilter::class, ['key' => 'num', 'values' => [24.24, 42.42]]))
        ->__toString()->toBe('(num=24.24 OR num=42.42)');
    expect(app(GroupFilter::class, ['key' => 'foo', 'values' => ['bar', 'baz']]))
        ->__toString()->toBe('(foo="bar" OR foo="baz")');
});
