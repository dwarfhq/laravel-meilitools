<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesScoutIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Validation\ValidationException;
use Meilisearch\Exceptions\CommunicationException;

/**
 * Synchronize index configured in Scout's MeiliSearch index settings.
 */
class SynchronizeScoutIndex implements SynchronizesScoutIndex
{
    public function __construct(
        protected SynchronizesIndex $synchronizeIndex,
        protected EnsuresIndexExists $ensureIndexExists,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws ValidationException    On validation failure.
     * @throws MeiliToolsException    When the index isn't configured, not using the MeiliSearch Scout driver
     *                                or the engine is unsupported.
     * @throws CommunicationException When connection to MeiliSearch fails.
     */
    public function __invoke(string $index, bool $pretend = false): array
    {
        $index = Helpers::scoutIndexName($index);
        $settings = Helpers::scoutIndexes()[$index] ?? null;

        throw_if(
            $settings === null,
            MeiliToolsException::class,
            \sprintf("No settings configured for index '%s' in 'scout.meilisearch.index-settings'", $index),
        );

        if (!$pretend) {
            ($this->ensureIndexExists)($index);
        }

        return ($this->synchronizeIndex)($index, $settings, $pretend);
    }
}
