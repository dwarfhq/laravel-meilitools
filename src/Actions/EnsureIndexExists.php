<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Meilisearch\Client;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Exceptions\CommunicationException;

/**
 * Ensure index exists.
 */
class EnsureIndexExists implements EnsuresIndexExists
{
    public function __construct(protected Client $client)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @throws MeiliToolsException    When not using the MeiliSearch Scout driver.
     * @throws CommunicationException When connection to MeiliSearch fails.
     */
    public function __invoke(string $index, array $options = []): void
    {
        Helpers::throwUnlessMeiliSearch();

        try {
            $this->client->index($index)->fetchRawInfo();
        } catch (ApiException) {
            $task = $this->client->createIndex($index, $options);
            $this->client->waitForTask($task['taskUid']);
        }
    }
}
