<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\CreatesIndex;
use Dwarf\MeiliTools\Helpers;
use Laravel\Scout\EngineManager;

/**
 * Create index.
 */
class CreateIndex implements CreatesIndex
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
     * @throws \Dwarf\MeiliTools\Exceptions\MeiliToolsException When not using the MeiliSearch Scout driver.
     * @throws \MeiliSearch\Exceptions\CommunicationException   When connection to MeiliSearch fails.
     */
    public function __invoke(string $index, array $options = []): array
    {
        Helpers::throwUnlessMeiliSearch();
        $engine = $this->manager->engine();
        $task = $engine->createIndex($index, $options);
        $engine->waitForTask($task['taskUid']);

        return $this->getIndexData($engine->getIndex($index));
    }
}
