<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

use Closure;
use DateTimeInterface;
use Dwarf\MeiliTools\Contracts\Filtering\FilterBuilder;

/**
 * Generates tenant tokens.
 */
interface GeneratesTenantToken
{
    /**
     * Generate a tenant token, which only allows searching the given indexes with the given filters.
     *
     * Search rules are keyed by index name, index pattern like `*`, or searchable model class. Each rule is a
     * filter expression, a filter builder, a closure receiving a filter builder, or null to allow every document.
     * A list of indexes allows every document in them.
     *
     * The token is signed with the given API key, or the configured tenant token key, which must allow searching
     * the indexes. The uid of the key is looked up when not given.
     *
     * @param array<string, (Closure(FilterBuilder): mixed)|FilterBuilder|string|null>|list<string> $searchRules
     */
    public function __invoke(
        array $searchRules,
        ?DateTimeInterface $expiresAt = null,
        ?string $apiKey = null,
        ?string $apiKeyUid = null,
    ): string;
}
