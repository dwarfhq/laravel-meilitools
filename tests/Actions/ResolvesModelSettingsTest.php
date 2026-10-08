<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\ResolvesModelSettings;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Dwarf\MeiliTools\Tests\Tools;

/**
 * Test ResolvesModelSettings::__invoke() method without any settings.
 */
test('without settings', function (): void {
    expect(resolve(ResolvesModelSettings::class)(Movie::class))->toBeEmpty();
});

/**
 * Test ResolvesModelSettings::__invoke() method with model settings.
 */
test('with model settings', function (): void {
    expect(resolve(ResolvesModelSettings::class)(MeiliMovie::class))->toBe(Tools::movieSettings());
});

/**
 * Test ResolvesModelSettings::__invoke() method with Scout settings.
 */
test('with scout settings', function (): void {
    config(['scout.meilisearch.index-settings' => [
        Movie::class => ['sortableAttributes' => ['rating'], 'searchCutoffMs' => 100],
    ]]);

    expect(resolve(ResolvesModelSettings::class)(Movie::class))
        ->toBe(['sortableAttributes' => ['rating'], 'searchCutoffMs' => 100])
    ;
});

/**
 * Test ResolvesModelSettings::__invoke() method with both Scout and model settings.
 */
test('with scout and model settings', function (): void {
    config(['scout.meilisearch.index-settings' => [
        MeiliMovie::class => ['sortableAttributes' => ['rating'], 'searchCutoffMs' => 100],
    ]]);

    // Model settings take precedence.
    $expected = array_replace(['searchCutoffMs' => 100], Tools::movieSettings());
    expect(resolve(ResolvesModelSettings::class)(MeiliMovie::class))->toEqual($expected);
});

/**
 * Test ResolvesModelSettings::__invoke() method with soft deletes enabled.
 */
test('with soft deletes enabled', function (): void {
    config(['scout.soft_delete' => true]);

    $settings = resolve(ResolvesModelSettings::class)(MeiliMovie::class);
    expect($settings['filterableAttributes'])->toBe(['__soft_deleted', 'release_date', 'rank']);

    // Movie doesn't use soft deletes.
    expect(resolve(ResolvesModelSettings::class)(Movie::class))->toBeEmpty();
});

/**
 * Test ResolvesModelSettings::__invoke() method with Scout settings keyed by index name.
 */
test('with scout index settings', function (): void {
    config(['scout.meilisearch.index-settings' => [
        'movies'     => ['sortableAttributes' => ['rating'], 'searchCutoffMs' => 100],
        Movie::class => ['sortableAttributes' => ['title']],
    ]]);

    // Settings keyed by model class take precedence.
    expect(resolve(ResolvesModelSettings::class)(Movie::class))
        ->toBe(['sortableAttributes' => ['title'], 'searchCutoffMs' => 100])
    ;
});
