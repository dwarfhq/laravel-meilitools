<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ResolvesModelSettings;
use Dwarf\MeiliTools\Contracts\Indexes\MeiliSettings;
use Dwarf\MeiliTools\Helpers;

/**
 * Resolve model index settings.
 */
class ResolveModelSettings implements ResolvesModelSettings
{
    /**
     * {@inheritDoc}
     *
     * Scout settings keyed by model class take precedence over settings keyed by the model's index name,
     * and settings from the model's `meiliSettings()` method take precedence over both.
     */
    public function __invoke(string $class): array
    {
        $settings = array_replace(
            Helpers::scoutIndexes()[Helpers::modelIndexName($class)] ?? [],
            Helpers::scoutIndexSettings()[$class] ?? [],
        );

        if (is_a($class, MeiliSettings::class, true)) {
            /** @var MeiliSettings $model */
            $model = resolve($class);
            $settings = array_replace($settings, $model->meiliSettings());
        }

        // Scout filters soft deleted models on '__soft_deleted', so it must be filterable.
        if (Helpers::usesSoftDelete($class)) {
            $filterable = \is_array($settings['filterableAttributes'] ?? null) ? $settings['filterableAttributes'] : [];
            $filterable = array_unique(['__soft_deleted', ...$filterable], \SORT_REGULAR);
            $settings['filterableAttributes'] = array_values($filterable);
        }

        return $settings;
    }
}
