<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Actions\Concerns\EnsuresModelIndex;
use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Contracts\Actions\ResetsIndex;
use Dwarf\MeiliTools\Contracts\Actions\ResetsModel;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Meilisearch\Exceptions\CommunicationException;

/**
 * Reset model index.
 */
class ResetModel implements ResetsModel
{
    use EnsuresModelIndex;

    public function __construct(
        protected ResetsIndex $resetIndex,
        protected EnsuresIndexExists $ensureIndexExists,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws MeiliToolsException    When not using the MeiliSearch Scout driver or the engine is unsupported.
     * @throws CommunicationException When connection to MeiliSearch fails.
     */
    public function __invoke(string $class, bool $pretend = false): array
    {
        $index = $pretend ? Helpers::modelIndexName($class) : $this->ensureModelIndex($class);

        return ($this->resetIndex)($index, $pretend);
    }
}
