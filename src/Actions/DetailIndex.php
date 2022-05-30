<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Laravel\Scout\EngineManager;

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
    private EngineManager $manager;

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
        // Convert synonyms from contract to array.
        $details['synonyms'] = $details['synonyms']->getIterator()->getArrayCopy();
        // Sort keys for consistency.
        ksort($details);

        return $details;
    }
}
