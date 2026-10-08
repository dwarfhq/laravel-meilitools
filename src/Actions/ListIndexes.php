<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Actions\Concerns\ExtractsIndexInformation;
use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Meilisearch\Client;
use Meilisearch\Contracts\IndexesQuery;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Exceptions\CommunicationException;

/**
 * List indexes.
 */
class ListIndexes implements ListsIndexes
{
    use ExtractsIndexInformation;

    /**
     * Number of indexes fetched per request.
     */
    protected const int LIMIT = 100;

    public function __construct(protected Client $client)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @throws MeiliToolsException    When not using the MeiliSearch Scout driver.
     * @throws CommunicationException When connection to MeiliSearch fails.
     */
    public function __invoke(bool $stats = false): array
    {
        Helpers::throwUnlessMeiliSearch();

        $indexes = [];
        $offset = 0;
        do {
            $results = $this->client->getIndexes(new IndexesQuery()->setOffset($offset)->setLimit(self::LIMIT));
            /** @var Indexes $index */
            foreach ($results->getResults() as $index) {
                $indexes[(string) $index->getUid()] = $this->getIndexData($index, $stats);
            }
            $offset += self::LIMIT;
        } while ($offset < $results->getTotal());

        ksort($indexes);

        return $indexes;
    }
}
