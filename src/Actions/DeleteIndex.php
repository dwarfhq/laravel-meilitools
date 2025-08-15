<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\DeletesIndex;
use Dwarf\MeiliTools\Helpers;
use Laravel\Scout\EngineManager;

/**
 * Delete index.
 */
class DeleteIndex implements DeletesIndex
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
    public function __invoke(string $index): void
    {
        Helpers::throwUnlessMeiliSearch();

        $engine = $this->manager->engine();
        $task = $engine->deleteIndex($index);
        $engine->waitForTask($task['taskUid']);
    }
}
