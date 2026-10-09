<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Closure;
use DateTimeInterface;
use Dwarf\MeiliTools\Contracts\Actions\GeneratesTenantToken;
use Dwarf\MeiliTools\Contracts\Filtering\FilterBuilder;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Meilisearch\Client;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Exceptions\CommunicationException;
use Meilisearch\Exceptions\InvalidArgumentException;

/**
 * Generate tenant token.
 */
class GenerateTenantToken implements GeneratesTenantToken
{
    public function __construct(protected Client $client)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @throws MeiliToolsException      When not using MeiliSearch, or no API key is given or configured.
     * @throws InvalidArgumentException When the search rules are empty or the expiry is in the past.
     * @throws CommunicationException   When connection to MeiliSearch fails while looking up the key uid.
     * @throws ApiException             When the key isn't found while looking up its uid.
     */
    public function __invoke(
        array $searchRules,
        ?DateTimeInterface $expiresAt = null,
        ?string $apiKey = null,
        ?string $apiKeyUid = null,
    ): string {
        Helpers::throwUnlessMeiliSearch();

        if ($apiKey === null) {
            $apiKey = config('meilitools.tenant_token.key');
            $apiKeyUid ??= config('meilitools.tenant_token.key_uid');
        }

        if (!\is_string($apiKey) || $apiKey === '') {
            throw new MeiliToolsException('An API key is required to generate tenant tokens');
        }

        return $this->client->generateTenantToken(
            \is_string($apiKeyUid) && $apiKeyUid !== '' ? $apiKeyUid : $this->keyUid($apiKey),
            $this->searchRules($searchRules),
            ['apiKey' => $apiKey, 'expiresAt' => $expiresAt],
        );
    }

    /**
     * Look up the uid of an API key.
     */
    protected function keyUid(string $apiKey): string
    {
        return (string) $this->client->getKey($apiKey)->getUid();
    }

    /**
     * Get the search rules in the format MeiliSearch expects.
     *
     * @param array<string, (Closure(FilterBuilder): mixed)|FilterBuilder|string|null>|list<string> $searchRules
     *
     * @return array<string, array{filter: string}|null>
     */
    protected function searchRules(array $searchRules): array
    {
        return collect($searchRules)
            ->mapWithKeys(function (mixed $rule, int|string $index): array {
                if (\is_int($index) && \is_string($rule)) {
                    return [$this->indexName($rule) => null];
                }

                $filter = $this->filter($rule);

                return [$this->indexName((string) $index) => $filter === '' ? null : ['filter' => $filter]];
            })
            ->all()
        ;
    }

    /**
     * Get the filter expression of a search rule.
     */
    protected function filter(mixed $rule): string
    {
        if ($rule instanceof Closure) {
            $builder = resolve(FilterBuilder::class);
            $rule($builder);
            $rule = $builder;
        }

        return match (true) {
            $rule instanceof FilterBuilder => $rule->toFilter(),
            \is_string($rule)              => $rule,
            default                        => '',
        };
    }

    /**
     * Get the index name of a search rule key, which may be a searchable model class.
     */
    protected function indexName(string $index): string
    {
        return Helpers::isSearchableModel($index) ? Helpers::modelIndexName($index) : $index;
    }
}
