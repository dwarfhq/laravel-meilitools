<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings;
use Dwarf\MeiliTools\Helpers;
use Laravel\Scout\EngineManager;

/**
 * Synchronize index.
 */
class SynchronizeIndex implements SynchronizesIndex
{
    /**
     * Scout engine manager.
     *
     * @var \Laravel\Scout\EngineManager
     */
    private EngineManager $manager;

    /**
     * Details index action.
     *
     * @var \Dwarf\MeiliTools\Contracts\Actions\DetailsIndex
     */
    private DetailsIndex $detailIndex;

    /**
     * Validates index settings action.
     *
     * @var \Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings
     */
    private ValidatesIndexSettings $validateSettings;

    /**
     * Constructor.
     *
     * @param \Laravel\Scout\EngineManager                               $manager          Scout engine manager.
     * @param \Dwarf\MeiliTools\Contracts\Actions\DetailsIndex           $detailIndex      Detail action.
     * @param \Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings $validateSettings Validate action.
     */
    public function __construct(
        EngineManager $manager,
        DetailsIndex $detailIndex,
        ValidatesIndexSettings $validateSettings
    ) {
        $this->manager = $manager;
        $this->detailIndex = $detailIndex;
        $this->validateSettings = $validateSettings;
    }

    /**
     * {@inheritDoc}
     *
     * @param bool $dryRun Whether to simulate running the action.
     *
     * @uses \Dwarf\MeiliTools\Contracts\Actions\DetailsIndex
     * @uses \Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings
     *
     * @throws \Illuminate\Validation\ValidationException       On validation failure.
     * @throws \Dwarf\MeiliTools\Exceptions\MeiliToolsException When not using the MeiliSearch Scout driver.
     * @throws \MeiliSearch\Exceptions\CommunicationException   When connection to MeiliSearch fails.
     * @throws \MeiliSearch\Exceptions\ApiException             When index is not found.
     */
    public function __invoke(string $index, array $settings, bool $dryRun = false): array
    {
        $validated = $this->validateSettings->validate($settings);
        // Quick return if no valid settings.
        if (empty($settings)) {
            return [];
        }

        // Fetch index settings.
        $details = ($this->detailIndex)($index);
        $defaults = Helpers::defaultSettings();

        // Compare and extract settings changes.
        $changes = array_filter($validated, function ($value, string $key) use ($details, $defaults) {
            // Straight comparison.
            if ($value === $details[$key]) {
                return false;
            }

            // Check if settings are default.
            if ($value === null && $details[$key] === $defaults[$key]) {
                return false;
            }

            return true;
        }, \ARRAY_FILTER_USE_BOTH);

        // Return if no changes exists.
        if (empty($changes)) {
            return [];
        }
        // Sort changes.
        ksort($changes);

        // Update index settings and wait for completion.
        if (!$dryRun) {
            $task = $this->manager->engine()->index($index)->updateSettings($changes);
            $this->manager->engine()->waitForTask($task['uid']);
        }

        return collect($changes)->map(fn ($value, $key) => ['old' => $details[$key], 'new' => $value])->all();
    }
}
