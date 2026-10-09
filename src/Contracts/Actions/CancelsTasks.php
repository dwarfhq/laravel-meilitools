<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Cancels tasks.
 */
interface CancelsTasks
{
    /**
     * Cancel the enqueued and processing tasks matching the filters, of which at least one is required.
     *
     * @param array{statuses?: list<string>, types?: list<string>, indexUids?: list<string>, uids?: list<int>} $filters
     *
     * @return int The number of canceled tasks.
     */
    public function __invoke(array $filters): int;
}
