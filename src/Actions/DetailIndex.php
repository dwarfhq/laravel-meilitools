<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Helpers;
use Laravel\Scout\EngineManager;
use Meilisearch\Contracts\Data;

/**
 * Detail index.
 */
class DetailIndex implements DetailsIndex
{
    /**
     * Scout engine manager.
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
        Helpers::throwUnlessMeiliSearch();

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
