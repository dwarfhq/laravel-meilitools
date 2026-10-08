<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Meilisearch\Client;
use Meilisearch\Contracts\Data;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Exceptions\CommunicationException;

/**
 * Detail index.
 */
class DetailIndex implements DetailsIndex
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
    public function __invoke(string $index): array
    {
        Helpers::throwUnlessMeiliSearch();

        // The SDK wraps some settings in iterable data objects.
        $details = array_map(
            fn (mixed $value): mixed => $value instanceof Data ? $value->getIterator()->getArrayCopy() : $value,
            $this->client->index($index)->getSettings(),
        );
        ksort($details);

        return $details;
    }
}
