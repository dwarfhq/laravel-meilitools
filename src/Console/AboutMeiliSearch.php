<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console;

use Dwarf\MeiliTools\Helpers;
use Meilisearch\Client;
use Throwable;

/**
 * Information about MeiliSearch for the `about` command.
 */
class AboutMeiliSearch
{
    public function __construct(protected Client $client)
    {
    }

    /**
     * Get the information, without failing when MeiliSearch is unreachable.
     *
     * @return array<string, string>
     */
    public function __invoke(): array
    {
        $information = [
            'Scout Driver' => (string) config('scout.driver'),
            'Host'         => (string) config('scout.meilisearch.host'),
        ];

        $version = Helpers::engineVersion();
        if ($version === null) {
            return $information + ['Version' => 'Unreachable'];
        }

        $information['Version'] = $version;

        // Stats require their own permission, which the API key may not have.
        try {
            $stats = $this->client->stats();
        } catch (Throwable) {
            return $information;
        }

        return $information + [
            'Indexes'       => (string) \count((array) ($stats['indexes'] ?? [])),
            'Database Size' => Helpers::formatBytes((int) ($stats['databaseSize'] ?? 0)),
        ];
    }
}
