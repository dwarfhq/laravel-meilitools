<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Contracts\Actions\ResetsIndex;
use Dwarf\MeiliTools\Contracts\Actions\ResetsModel;

/**
 * Reset model index.
 */
class ResetModel implements ResetsModel
{
    /**
     * Resets index action.
     */
    protected ResetsIndex $resetIndex;

    /**
     * Ensures index exists action.
     */
    protected EnsuresIndexExists $ensureIndexExists;

    /**
     * Constructor.
     *
     * @param \Dwarf\MeiliTools\Contracts\Actions\ResetsIndex        $resetIndex        Reset action.
     * @param \Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists $ensureIndexExists Action ensuring index exists.
     */
    public function __construct(ResetsIndex $resetIndex, EnsuresIndexExists $ensureIndexExists)
    {
        $this->resetIndex = $resetIndex;
        $this->ensureIndexExists = $ensureIndexExists;
    }

    /**
     * {@inheritDoc}
     *
     * @param bool $pretend Whether to pretend running the action.
     *
     * @uses \Dwarf\MeiliTools\Contracts\Actions\ResetsIndex
     * @uses \Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists
     *
     * @throws \Dwarf\MeiliTools\Exceptions\MeiliToolsException When not using the MeiliSearch Scout driver.
     * @throws \MeiliSearch\Exceptions\CommunicationException   When connection to MeiliSearch fails.
     */
    public function __invoke(string $class, bool $pretend = false): array
    {
        $model = app($class);
        $index = $model->searchableAs();
        $primaryKey = $model->getKeyName();

        ($this->ensureIndexExists)($index, compact('primaryKey'));

        return ($this->resetIndex)($index, $pretend);
    }
}
