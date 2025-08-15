<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ViewsIndex;
use Dwarf\MeiliTools\Helpers;
use Laravel\Scout\EngineManager;
use MeiliSearch\Endpoints\Indexes;

/**
 * List indexes.
 */
class ViewIndex implements ViewsIndex
{
    use Concerns\ExtractsIndexInformation;

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
     * @param bool $stats Whether to include index stats.
     *
     * @throws \Dwarf\MeiliTools\Exceptions\MeiliToolsException When not using the MeiliSearch Scout driver.
     * @throws \MeiliSearch\Exceptions\CommunicationException   When connection to MeiliSearch fails.
     * @throws \MeiliSearch\Exceptions\ApiException             When index is not found.
     */
    public function __invoke(string $index, bool $stats = false): array
    {
        Helpers::throwUnlessMeiliSearch();

        $index = $this->manager->engine()->getIndex($index);

        return $this->getIndexData($index, $stats);
    }
}
