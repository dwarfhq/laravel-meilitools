<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\GeneratesTenantToken;
use Dwarf\MeiliTools\Contracts\Filtering\FilterBuilder;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Filtering\FilterBuilder as ConcreteFilterBuilder;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Meilisearch\Client;
use Meilisearch\Endpoints\Keys;
use Meilisearch\Exceptions\InvalidArgumentException;

/**
 * Create a search key for the testing indexes.
 */
function createSearchKey(): Keys
{
    return resolve(Client::class)->createKey([
        'actions'   => ['search'],
        'indexes'   => ['testing-*'],
        'expiresAt' => null,
    ]);
}

/**
 * Get the payload of a tenant token.
 *
 * @return array<string, mixed>
 */
function tokenPayload(string $token): array
{
    return json_decode(base64_decode(strtr(explode('.', $token)[1], '-_', '+/'), true), true);
}

/**
 * Search an index with a tenant token, returning the ids of the hits.
 *
 * @return list<int>
 */
function searchWithToken(string $token, string $index): array
{
    $hits = new Client((string) config('scout.meilisearch.host'), $token)->index($index)->search('')->getHits();
    $ids = array_column($hits, 'id');
    sort($ids);

    return $ids;
}

/**
 * Test GeneratesTenantToken::__invoke() method.
 */
test('invoke', function (): void {
    $key = createSearchKey();
    config(['meilitools.tenant_token.key' => $key->getKey()]);

    try {
        $this->withIndex('testing-tenants', function (): void {
            $client = resolve(Client::class);
            $index = $client->index('testing-tenants');
            $client->waitForTask($index->updateFilterableAttributes(['tenant'])['taskUid']);
            $client->waitForTask($index->addDocuments([
                ['id' => 1, 'tenant' => 'a'],
                ['id' => 2, 'tenant' => 'b'],
                ['id' => 3, 'tenant' => 'a'],
            ], 'id')['taskUid']);

            $generate = resolve(GeneratesTenantToken::class);

            expect(searchWithToken($generate(['testing-tenants' => "tenant = 'a'"]), 'testing-tenants'))->toBe([1, 3])
                ->and(searchWithToken($generate(['testing-tenants' => null]), 'testing-tenants'))->toBe([1, 2, 3])
                ->and(searchWithToken($generate(['testing-*']), 'testing-tenants'))->toBe([1, 2, 3])
                ->and(searchWithToken(
                    $generate(['*' => fn (FilterBuilder $filter) => $filter->where('tenant', 'b')]),
                    'testing-tenants',
                ))->toBe([2])
            ;
        });
    } finally {
        resolve(Client::class)->deleteKey((string) $key->getUid());
    }
});

/**
 * Test GeneratesTenantToken::__invoke() method with search rules.
 */
test('search rules', function (): void {
    $key = createSearchKey();

    try {
        $token = resolve(GeneratesTenantToken::class)(
            [
                Movie::class => new ConcreteFilterBuilder()->where('rating', '>', 3),
                'testing-a'  => 'tenant = 1',
                'testing-b'  => fn (FilterBuilder $filter) => $filter->whereIn('tenant', [1, 2]),
                'testing-c'  => fn (FilterBuilder $filter) => $filter,
                'testing-d'  => null,
            ],
            apiKey: (string) $key->getKey(),
        );

        expect(tokenPayload($token))->toBe([
            'apiKeyUid'   => $key->getUid(),
            'searchRules' => [
                'testing-movies' => ['filter' => 'rating > 3'],
                'testing-a'      => ['filter' => 'tenant = 1'],
                'testing-b'      => ['filter' => 'tenant IN [1, 2]'],
                'testing-c'      => null,
                'testing-d'      => null,
            ],
        ]);
    } finally {
        resolve(Client::class)->deleteKey((string) $key->getUid());
    }
});

/**
 * Test GeneratesTenantToken::__invoke() method with expiry and configured key uid.
 */
test('with expiry and key uid', function (): void {
    config([
        'meilitools.tenant_token.key'     => 'configured-api-key',
        'meilitools.tenant_token.key_uid' => 'configured-uid',
    ]);
    $expiresAt = now()->addHour();

    $configured = resolve(GeneratesTenantToken::class)(['*'], $expiresAt);
    $given = resolve(GeneratesTenantToken::class)(['*'], apiKey: 'given-api-key', apiKeyUid: 'given-uid');

    expect(tokenPayload($configured))->toBe([
        'apiKeyUid'   => 'configured-uid',
        'searchRules' => ['*' => null],
        'exp'         => $expiresAt->getTimestamp(),
    ])
        ->and(tokenPayload($given)['apiKeyUid'])->toBe('given-uid')
    ;
});

/**
 * Test GeneratesTenantToken::__invoke() method with an expiry in the past.
 */
test('with expired date', function (): void {
    resolve(GeneratesTenantToken::class)(['*'], now()->subMinute(), 'given-api-key', 'given-uid');
})->throws(InvalidArgumentException::class);

/**
 * Test GeneratesTenantToken::__invoke() method without an API key.
 */
test('without key', function (): void {
    resolve(GeneratesTenantToken::class)(['*']);
})->throws(MeiliToolsException::class, 'An API key is required to generate tenant tokens');
