<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Support\Arr;
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
     * @param bool $pretend Whether to pretend running the action.
     *
     * @uses \Dwarf\MeiliTools\Contracts\Actions\DetailsIndex
     * @uses \Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings
     *
     * @throws \Illuminate\Validation\ValidationException       On validation failure.
     * @throws \Dwarf\MeiliTools\Exceptions\MeiliToolsException When not using the MeiliSearch Scout driver.
     * @throws \MeiliSearch\Exceptions\CommunicationException   When connection to MeiliSearch fails.
     * @throws \MeiliSearch\Exceptions\ApiException             When index is not found.
     */
    public function __invoke(string $index, array $settings, bool $pretend = false): array
    {
        // Get engine version.
        $engine = $this->manager->engine();
        $version = $engine->version()['pkgVersion'] ?? null;

        $validated = $this->validateSettings->validate($settings, $version);
        // Quick return if no valid settings.
        if (empty($validated)) {
            return [];
        }

        // Fetch index settings.
        $details = ($this->detailIndex)($index);
        $defaults = Helpers::defaultSettings($version);
        $sorted = Helpers::sortSettings($validated);

        // Remove typo tolerance if not present in defaults.
        if (!\array_key_exists('typoTolerance', $defaults)) {
            unset($sorted['typoTolerance']);
        }

        // Special handling for typo tolerance.
        if (\array_key_exists('typoTolerance', $sorted) && \is_array($sorted['typoTolerance'])) {
            $sorted['typoTolerance'] = array_filter(
                $sorted['typoTolerance'],
                function ($value, string $key) use ($details, $defaults) {
                    return $this->filter($value, $details['typoTolerance'][$key], $defaults['typoTolerance'][$key]);
                },
                \ARRAY_FILTER_USE_BOTH
            );
            $keys = array_keys($sorted['typoTolerance']);
            $defaults['typoTolerance'] = Arr::only($defaults['typoTolerance'], $keys);
            $details['typoTolerance'] = Arr::only($details['typoTolerance'], $keys);
        }

        // Compare and extract settings changes.
        $changes = array_filter($sorted, function ($value, string $key) use ($details, $defaults) {
            return $this->filter($value, $details[$key], $defaults[$key]);
        }, \ARRAY_FILTER_USE_BOTH);

        // Return if no changes exists.
        if (empty($changes)) {
            return [];
        }
        // Sort changes.
        ksort($changes);

        // Update index settings and wait for completion.
        if (!$pretend) {
            $task = $engine->index($index)->updateSettings($changes);
            $engine->waitForTask($task['uid']);
        }

        return collect($changes)->map(fn ($value, $key) => ['old' => $details[$key], 'new' => $value])->all();
    }

    /**
     * Whether the value should be filtered as changed.
     *
     * @param mixed $value
     * @param mixed $detail
     * @param mixed $default
     *
     * @return bool
     */
    protected function filter($value, $detail, $default): bool
    {
        // Straight comparison.
        if ($value === $detail) {
            return false;
        }

        // Check if settings are default.
        if ($value === null && $detail === $default) {
            return false;
        }

        return true;
    }
}
