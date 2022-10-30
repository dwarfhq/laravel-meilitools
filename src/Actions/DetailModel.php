<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;

/**
 * Detail model index.
 */
class DetailModel implements DetailsModel
{
    /**
     * Details index action.
     *
     * @var \Dwarf\MeiliTools\Contracts\Actions\DetailsIndex
     */
    protected DetailsIndex $detailIndex;

    /**
     * Ensures index exists action.
     *
     * @var \Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists
     */
    protected EnsuresIndexExists $ensureIndexExists;

    /**
     * Constructor.
     *
     * @param \Dwarf\MeiliTools\Contracts\Actions\DetailsIndex       $detailIndex       Detail action.
     * @param \Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists $ensureIndexExists Action ensuring index exists.
     */
    public function __construct(DetailsIndex $detailIndex, EnsuresIndexExists $ensureIndexExists)
    {
        $this->detailIndex = $detailIndex;
        $this->ensureIndexExists = $ensureIndexExists;
    }

    /**
     * {@inheritDoc}
     *
     * @uses \Dwarf\MeiliTools\Contracts\Actions\DetailsIndex
     * @uses \Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists
     *
     * @throws \Dwarf\MeiliTools\Exceptions\MeiliToolsException When not using the MeiliSearch Scout driver.
     * @throws \MeiliSearch\Exceptions\CommunicationException   When connection to MeiliSearch fails.
     */
    public function __invoke(string $class): array
    {
        $model = app($class);
        $index = $model->searchableAs();
        $primaryKey = $model->getKeyName();

        ($this->ensureIndexExists)($index, compact('primaryKey'));

        return ($this->detailIndex)($index);
    }
}
