<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ResetsIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Helpers;
use Laravel\Scout\EngineManager;

/**
 * Reset index.
 */
class ResetIndex implements ResetsIndex
{
    /**
     * Scout engine manager.
     */
    protected EngineManager $manager;

    /**
     * Synchronizes index action.
     */
    protected SynchronizesIndex $synchronizeIndex;

    /**
     * Constructor.
     *
     * @param \Laravel\Scout\EngineManager                          $manager          Scout engine manager.
     * @param \Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex $synchronizeIndex Synchronize action.
     */
    public function __construct(EngineManager $manager, SynchronizesIndex $synchronizeIndex)
    {
        $this->manager = $manager;
        $this->synchronizeIndex = $synchronizeIndex;
    }

    /**
     * {@inheritDoc}
     *
     * @uses \Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex
     *
     * @throws \Dwarf\MeiliTools\Exceptions\MeiliToolsException When not using the MeiliSearch Scout driver.
     * @throws \MeiliSearch\Exceptions\CommunicationException   When connection to MeiliSearch fails.
     * @throws \MeiliSearch\Exceptions\ApiException             When index is not found.
     */
    public function __invoke(string $index, bool $pretend = false): array
    {
        // Fetch index settings.
        $settings = Helpers::defaultSettings(Helpers::engineVersion());
        array_walk($settings, fn (&$value) => $value = null);

        return ($this->synchronizeIndex)($index, $settings, $pretend);
    }
}
