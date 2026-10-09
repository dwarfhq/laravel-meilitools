<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ViewsStats;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Meilisearch\Client;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Exceptions\CommunicationException;

/**
 * View stats.
 */
class ViewStats implements ViewsStats
{
    public function __construct(protected Client $client)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @throws MeiliToolsException    When not using the MeiliSearch Scout driver.
     * @throws CommunicationException When connection to MeiliSearch fails.
     * @throws ApiException           When index is not found.
     */
    public function __invoke(?string $index = null): array
    {
        Helpers::throwUnlessMeiliSearch();

        return $index === null ? $this->client->stats() : $this->client->index($index)->stats();
    }
}
