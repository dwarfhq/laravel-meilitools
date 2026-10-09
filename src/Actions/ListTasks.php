<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Actions\Concerns\BuildsTaskQueries;
use Dwarf\MeiliTools\Contracts\Actions\ListsTasks;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Meilisearch\Client;
use Meilisearch\Contracts\TasksQuery;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Exceptions\CommunicationException;

/**
 * List tasks.
 */
class ListTasks implements ListsTasks
{
    use BuildsTaskQueries;

    public function __construct(protected Client $client)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @throws MeiliToolsException    When not using the MeiliSearch Scout driver.
     * @throws CommunicationException When connection to MeiliSearch fails.
     * @throws ApiException           When a filter is invalid.
     */
    public function __invoke(array $filters = [], int $limit = 20): array
    {
        Helpers::throwUnlessMeiliSearch();

        $query = $this->applyTaskFilters(new TasksQuery()->setLimit($limit), $filters);

        return array_values($this->client->getTasks($query)->getResults());
    }
}
