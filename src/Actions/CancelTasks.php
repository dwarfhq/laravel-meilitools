<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Actions\Concerns\BuildsTaskQueries;
use Dwarf\MeiliTools\Contracts\Actions\CancelsTasks;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Meilisearch\Client;
use Meilisearch\Contracts\CancelTasksQuery;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Exceptions\CommunicationException;

/**
 * Cancel tasks.
 */
class CancelTasks implements CancelsTasks
{
    use BuildsTaskQueries;

    public function __construct(protected Client $client)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @throws MeiliToolsException    When not using the MeiliSearch Scout driver, or no filters are given.
     * @throws CommunicationException When connection to MeiliSearch fails.
     * @throws ApiException           When a filter is invalid.
     */
    public function __invoke(array $filters): int
    {
        Helpers::throwUnlessMeiliSearch();

        if (array_filter($filters) === []) {
            throw new MeiliToolsException('At least one filter is required to cancel tasks');
        }

        $task = $this->client->cancelTasks($this->applyTaskFilters(new CancelTasksQuery(), $filters));
        $task = $this->client->waitForTask($task['taskUid']);

        return (int) ($task['details']['canceledTasks'] ?? 0);
    }
}
