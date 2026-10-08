<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Actions\Concerns\EnsuresModelIndex;
use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Contracts\Actions\ViewsIndex;
use Dwarf\MeiliTools\Contracts\Actions\ViewsModel;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Meilisearch\Exceptions\CommunicationException;

/**
 * View model index.
 */
class ViewModel implements ViewsModel
{
    use EnsuresModelIndex;

    public function __construct(
        protected ViewsIndex $viewIndex,
        protected EnsuresIndexExists $ensureIndexExists,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws MeiliToolsException    When not using the MeiliSearch Scout driver.
     * @throws CommunicationException When connection to MeiliSearch fails.
     */
    public function __invoke(string $class, bool $stats = false): array
    {
        return ($this->viewIndex)($this->ensureModelIndex($class), $stats);
    }
}
