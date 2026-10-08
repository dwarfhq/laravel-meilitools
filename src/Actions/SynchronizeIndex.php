<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Validation\ValidationException;
use Meilisearch\Client;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Exceptions\CommunicationException;

/**
 * Synchronize index.
 */
class SynchronizeIndex implements SynchronizesIndex
{
    /**
     * Settings which MeiliSearch partially updates, merging given keys into the existing values.
     *
     * @var list<string>
     */
    protected const array MERGED_SETTINGS = [
        'faceting',
        'pagination',
        'typoTolerance',
        'typoTolerance.minWordSizeForTypos',
    ];

    /**
     * MeiliSearch engine version, fetched once per instance.
     */
    protected ?string $engineVersion = null;

    public function __construct(
        protected Client $client,
        protected DetailsIndex $detailIndex,
        protected ValidatesIndexSettings $validateSettings,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws ValidationException    On validation failure.
     * @throws MeiliToolsException    When not using the MeiliSearch Scout driver or the engine is unsupported.
     * @throws CommunicationException When connection to MeiliSearch fails.
     * @throws ApiException           When index is not found.
     */
    public function __invoke(string $index, array $settings, bool $pretend = false): array
    {
        $validated = $this->validateSettings->validate($settings);
        if ($validated === []) {
            return [];
        }

        Helpers::throwUnlessMeiliSearch();
        $this->engineVersion ??= Helpers::engineVersion();
        Helpers::throwUnlessSupportedEngine($this->engineVersion);

        $details = ($this->detailIndex)($index);
        $defaults = Helpers::defaultSettings();

        $changes = [];
        foreach (Helpers::sortSettings($validated) as $key => $value) {
            [$changed, $new, $old] = $this->compare(
                $key,
                $value,
                $details[$key] ?? null,
                $defaults[$key] ?? null,
            );
            if ($changed) {
                $changes[$key] = ['old' => $old, 'new' => $new];
            }
        }

        if (!$pretend && $changes !== []) {
            $task = $this->client
                ->index($index)
                ->updateSettings(array_map(fn (array $change): mixed => $change['new'], $changes))
            ;
            $this->client->waitForTask($task['taskUid']);
        }

        return $changes;
    }

    /**
     * Compare a setting value with the current index value.
     *
     * Merged settings are compared key by key, so only changed keys are included.
     *
     * @return array{bool, mixed, mixed} Whether the value changed, the new value and the old value.
     */
    protected function compare(string $path, mixed $value, mixed $detail, mixed $default): array
    {
        if (\in_array($path, self::MERGED_SETTINGS, true) && \is_array($value) && \is_array($detail)) {
            $new = [];
            $old = [];
            foreach ($value as $key => $item) {
                [$changed, $newItem, $oldItem] = $this->compare(
                    $path . '.' . $key,
                    $item,
                    $detail[$key] ?? null,
                    \is_array($default) ? $default[$key] ?? null : null,
                );
                if ($changed) {
                    $new[$key] = $newItem;
                    $old[$key] = $oldItem;
                }
            }

            return [$new !== [], $new, $old];
        }

        // Unchanged if identical, or if resetting a setting which is already default.
        $unchanged = $value === $detail || ($value === null && $detail === $default);

        return [!$unchanged, $value, $detail];
    }
}
