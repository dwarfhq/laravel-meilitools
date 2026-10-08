<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ResetsIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Exceptions\CommunicationException;

/**
 * Reset index.
 */
class ResetIndex implements ResetsIndex
{
    public function __construct(protected SynchronizesIndex $synchronizeIndex)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @throws MeiliToolsException    When not using the MeiliSearch Scout driver or the engine is unsupported.
     * @throws CommunicationException When connection to MeiliSearch fails.
     * @throws ApiException           When index is not found.
     */
    public function __invoke(string $index, bool $pretend = false): array
    {
        // Resetting a setting is done by setting it to null.
        $settings = array_fill_keys(array_keys(Helpers::defaultSettings()), null);

        return ($this->synchronizeIndex)($index, $settings, $pretend);
    }
}
