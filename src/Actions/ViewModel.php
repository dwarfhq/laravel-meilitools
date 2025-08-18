<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Contracts\Actions\ViewsIndex;
use Dwarf\MeiliTools\Contracts\Actions\ViewsModel;

/**
 * View model index.
 */
class ViewModel implements ViewsModel
{
    /**
     * Views index action.
     */
    protected ViewsIndex $viewIndex;

    /**
     * Ensures index exists action.
     */
    protected EnsuresIndexExists $ensureIndexExists;

    /**
     * Constructor.
     *
     * @param \Dwarf\MeiliTools\Contracts\Actions\ViewsIndex         $viewIndex         View action.
     * @param \Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists $ensureIndexExists Action ensuring index exists.
     */
    public function __construct(ViewsIndex $viewIndex, EnsuresIndexExists $ensureIndexExists)
    {
        $this->viewIndex = $viewIndex;
        $this->ensureIndexExists = $ensureIndexExists;
    }

    /**
     * {@inheritDoc}
     *
     * @uses \Dwarf\MeiliTools\Contracts\Actions\ViewsIndex
     * @uses \Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists
     *
     * @param bool $stats Whether to include index stats.
     *
     * @throws \Dwarf\MeiliTools\Exceptions\MeiliToolsException When not using the MeiliSearch Scout driver.
     * @throws \MeiliSearch\Exceptions\CommunicationException   When connection to MeiliSearch fails.
     */
    public function __invoke(string $class, bool $stats = false): array
    {
        $model = app($class);
        $index = $model->searchableAs();
        $primaryKey = $model->getKeyName();

        ($this->ensureIndexExists)($index, compact('primaryKey'));

        return ($this->viewIndex)($index, $stats);
    }
}
