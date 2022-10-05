<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Laravel\Scout\EngineManager;
use MeiliSearch\Endpoints\Indexes;

/**
 * Detail list.
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
     * @throws \MeiliSearch\Exceptions\ApiException             When index is not found.
     */
    public function __invoke(): array
    {
        if ($this->manager->getDefaultDriver() !== 'meilisearch') {
            throw new MeiliToolsException('Scout must be using the MeiliSearch driver');
        }

        $indexes = $this->manager->engine()->getAllIndexes();
        // Convert iterator objects from contract to array.
        $indexes = collect($indexes->getResults())->mapWithKeys(function ($value) {
            return $value instanceof Indexes ? $this->getIndexData($value) : $value;
        })->toArray();
        // Sort keys for consistency.
        ksort($indexes);

        return $indexes;
    }

    private function getIndexData(Indexes $index): array
    {
        $stats = $index->stats();

        return [
            $index->getUid() => [
                'uid' => $index->getUid(),
                'primaryKey' => $index->getPrimaryKey(),
                'createdAt' => $index->getCreatedAtString(),
                'updatedAt' => $index->getUpdatedAtString(),
                'numberOfDocuments' => $stats['numberOfDocuments'],
                'isIndexing' => $stats['isIndexing'],
            ],
        ];
    }
}
