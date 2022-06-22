<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Laravel\Scout\EngineManager;
use MeiliSearch\Contracts\Data;

/**
 * Detail index.
 */
class DetailIndex implements DetailsIndex
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
    public function __invoke(string $index): array
    {
        if ($this->manager->getDefaultDriver() !== 'meilisearch') {
            throw new MeiliToolsException('Scout must be using the MeiliSearch driver');
        }

        $details = $this->manager->engine()->index($index)->getSettings();
        // Convert iterator objects from contract to array.
        $details = array_map(function ($value) {
            return $value instanceof Data ? $value->getIterator()->getArrayCopy() : $value;
        }, $details);
        // Sort keys for consistency.
        ksort($details);

        return $details;
    }
}
