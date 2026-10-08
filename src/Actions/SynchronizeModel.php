<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Actions\Concerns\EnsuresModelIndex;
use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Contracts\Actions\ResolvesModelSettings;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Illuminate\Validation\ValidationException;
use Meilisearch\Exceptions\CommunicationException;

/**
 * Synchronize model index.
 */
class SynchronizeModel implements SynchronizesModel
{
    use EnsuresModelIndex;

    public function __construct(
        protected SynchronizesIndex $synchronizeIndex,
        protected EnsuresIndexExists $ensureIndexExists,
        protected ResolvesModelSettings $resolveSettings,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws ValidationException    On validation failure.
     * @throws MeiliToolsException    When not using the MeiliSearch Scout driver or the engine is unsupported.
     * @throws CommunicationException When connection to MeiliSearch fails.
     */
    public function __invoke(string $class, bool $pretend = false): array
    {
        $settings = ($this->resolveSettings)($class);
        $index = $this->ensureModelIndex($class);

        return ($this->synchronizeIndex)($index, $settings, $pretend);
    }
}
