<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Meilisearch\Client;

/**
 * Get the MeiliSearch section of the `about` command.
 *
 * @return array<string, string>
 */
function aboutMeiliSearch(): array
{
    Artisan::call('about', ['--only' => 'meilisearch', '--json' => true]);

    return json_decode(Artisan::output(), true)['meilisearch'] ?? [];
}

/**
 * Test the MeiliSearch section of the `about` command.
 */
test('about', function (): void {
    $about = aboutMeiliSearch();

    expect($about)->toHaveKeys(['scout_driver', 'host', 'version', 'indexes', 'database_size'])
        ->and($about['scout_driver'])->toBe('meilisearch')
        ->and($about['version'])->toMatch('/^\d+\.\d+\.\d+/')
        ->and($about['database_size'])->toMatch('/^\d+(\.\d+)? [KMGT]?B$/')
    ;
});

/**
 * Test the MeiliSearch section of the `about` command when MeiliSearch is unreachable.
 */
test('unreachable', function (): void {
    config(['scout.meilisearch.host' => 'http://localhost:7777']);
    app()->forgetInstance(Client::class);

    expect(aboutMeiliSearch())->toBe([
        'scout_driver' => 'meilisearch',
        'host'         => 'http://localhost:7777',
        'version'      => 'Unreachable',
    ]);
});

/**
 * Test the MeiliSearch section of the `about` command when stats aren't available.
 */
test('without stats', function (): void {
    $client = Mockery::mock(Client::class);
    $client->shouldReceive('version')->andReturn(['pkgVersion' => '1.54.3']);
    $client->shouldReceive('stats')->andThrow(new RuntimeException('The provided API key is invalid.'));
    app()->instance(Client::class, $client);

    expect(aboutMeiliSearch())->toBe([
        'scout_driver' => 'meilisearch',
        'host'         => config('scout.meilisearch.host'),
        'version'      => '1.54.3',
    ]);
});
