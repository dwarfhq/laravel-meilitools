<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Actions\Concerns\EnsuresModelIndex;
use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Meilisearch\Exceptions\CommunicationException;

/**
 * Detail model index.
 */
class DetailModel implements DetailsModel
{
    use EnsuresModelIndex;

    public function __construct(
        protected DetailsIndex $detailIndex,
        protected EnsuresIndexExists $ensureIndexExists,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws MeiliToolsException    When not using the MeiliSearch Scout driver.
     * @throws CommunicationException When connection to MeiliSearch fails.
     */
    public function __invoke(string $class): array
    {
        return ($this->detailIndex)($this->ensureModelIndex($class));
    }
}
