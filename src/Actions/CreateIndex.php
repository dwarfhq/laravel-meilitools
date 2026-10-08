<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Actions\Concerns\ExtractsIndexInformation;
use Dwarf\MeiliTools\Contracts\Actions\CreatesIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Meilisearch\Client;
use Meilisearch\Exceptions\CommunicationException;

/**
 * Create index.
 */
class CreateIndex implements CreatesIndex
{
    use ExtractsIndexInformation;

    public function __construct(protected Client $client)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @throws MeiliToolsException    When not using the MeiliSearch Scout driver.
     * @throws CommunicationException When connection to MeiliSearch fails.
     */
    public function __invoke(string $index, array $options = []): array
    {
        Helpers::throwUnlessMeiliSearch();

        $task = $this->client->createIndex($index, $options);
        $this->client->waitForTask($task['taskUid']);

        return $this->getIndexData($this->client->getIndex($index));
    }
}
