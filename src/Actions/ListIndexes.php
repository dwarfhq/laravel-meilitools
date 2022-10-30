<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Helpers;
use Laravel\Scout\EngineManager;
use MeiliSearch\Endpoints\Indexes;

/**
 * List indexes.
 */
class ListIndexes implements ListsIndexes
{
    use Concerns\ExtractsIndexInformation;

    /**
     * Scout engine manager.
     *
     * @var \Laravel\Scout\EngineManager
     */
    protected EngineManager $manager;

    /**
     * Constructor.
     *
     * @param \Laravel\Scout\EngineManager $manager Scout engine manager.
     */
    public function __construct(EngineManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     *
     * @param bool $stats Whether to include index stats.
     *
     * @throws \Dwarf\MeiliTools\Exceptions\MeiliToolsException When not using the MeiliSearch Scout driver.
     * @throws \MeiliSearch\Exceptions\CommunicationException   When connection to MeiliSearch fails.
     */
    public function __invoke(bool $stats = false): array
    {
        Helpers::throwUnlessMeiliSearch();

        $indexes = $this->manager->engine()->getAllIndexes()->getResults();
        // Convert iterator objects from contract to array.
        return collect($indexes)
            ->mapWithKeys(fn (Indexes $index) => [$index->getUid() => $this->getIndexData($index, $stats)])
            ->sortKeys()
            ->all()
        ;
    }
}
