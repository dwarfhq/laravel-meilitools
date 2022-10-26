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
     * @throws \Dwarf\MeiliTools\Exceptions\MeiliToolsException When not using the MeiliSearch Scout driver.
     * @throws \MeiliSearch\Exceptions\CommunicationException   When connection to MeiliSearch fails.
     */
    public function __invoke(): array
    {
        Helpers::throwUnlessMeiliSearch();

        $indexes = $this->manager->engine()->getAllIndexes()->getResults();
        // Convert iterator objects from contract to array.
        return collect($indexes)
            ->mapWithKeys(fn (Indexes $index) => $this->getIndexData($index))
            ->sortKeys()
            ->all()
        ;
    }

    /**
     * Get index data and stats.
     *
     * @param \MeiliSearch\Endpoints\Indexes $index Index.
     *
     * @return array
     */
    protected function getIndexData(Indexes $index): array
    {
        $stats = $index->stats();

        return [
            $index->getUid() => [
                'uid'               => $index->getUid(),
                'primaryKey'        => $index->getPrimaryKey(),
                'createdAt'         => $index->getCreatedAtString(),
                'updatedAt'         => $index->getUpdatedAtString(),
                'numberOfDocuments' => $stats['numberOfDocuments'],
                'isIndexing'        => $stats['isIndexing'],
            ],
        ];
    }
}
