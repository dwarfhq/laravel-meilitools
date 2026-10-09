<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools;

use Brick\VarExporter\VarExporter;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Searchable;
use Meilisearch\Client;
use Throwable;

class Helpers
{
    /**
     * Minimum supported MeiliSearch engine version.
     */
    public const string MINIMUM_ENGINE_VERSION = '1.36.0';

    /**
     * Whether Scout is using the MeiliSearch driver.
     */
    public static function usingMeiliSearch(): bool
    {
        return resolve(EngineManager::class)->getDefaultDriver() === 'meilisearch';
    }

    /**
     * Throw exception unless Scout is using the MeiliSearch driver.
     *
     * @throws MeiliToolsException
     */
    public static function throwUnlessMeiliSearch(): void
    {
        throw_unless(
            self::usingMeiliSearch(),
            MeiliToolsException::class,
            'Scout must be using the MeiliSearch driver',
        );
    }

    /**
     * Throw exception if the MeiliSearch engine version is unsupported.
     *
     * @throws MeiliToolsException
     */
    public static function throwUnlessSupportedEngine(?string $version): void
    {
        throw_if(
            $version !== null && version_compare($version, self::MINIMUM_ENGINE_VERSION, '<'),
            MeiliToolsException::class,
            \sprintf(
                'MeiliSearch engine version %s or newer is required, found %s',
                self::MINIMUM_ENGINE_VERSION,
                $version,
            ),
        );
    }

    /**
     * Default MeiliSearch index settings.
     *
     * Experimental settings and embedders are not managed, and therefore not included.
     *
     * @return array<string, mixed>
     */
    public static function defaultSettings(): array
    {
        return [
            'dictionary'          => [],
            'displayedAttributes' => ['*'],
            'distinctAttribute'   => null,
            'facetSearch'         => true,
            'faceting'            => [
                'maxValuesPerFacet' => 100,
                'sortFacetValuesBy' => ['*' => 'alpha'],
            ],
            'filterableAttributes' => [],
            'localizedAttributes'  => null,
            'nonSeparatorTokens'   => [],
            'pagination'           => ['maxTotalHits' => 1000],
            'prefixSearch'         => 'indexingTime',
            'proximityPrecision'   => 'byWord',
            'rankingRules'         => [
                'words',
                'typo',
                'proximity',
                'attributeRank',
                'sort',
                'wordPosition',
                'exactness',
            ],
            'searchCutoffMs'       => null,
            'searchableAttributes' => ['*'],
            'separatorTokens'      => [],
            'sortableAttributes'   => [],
            'stopWords'            => [],
            'synonyms'             => [],
            'typoTolerance'        => [
                'enabled'             => true,
                'minWordSizeForTypos' => [
                    'oneTypo'  => 5,
                    'twoTypos' => 9,
                ],
                'disableOnWords'      => [],
                'disableOnAttributes' => [],
                'disableOnNumbers'    => false,
            ],
        ];
    }

    /**
     * Sort MeiliSearch settings.
     *
     * Certain settings are automatically sorted and deduplicated by MeiliSearch,
     * so we do it the same way to correctly compare data.
     *
     * @param array<string, mixed> $settings
     *
     * @return array<string, mixed>
     */
    public static function sortSettings(array $settings): array
    {
        ksort($settings);

        foreach ($settings as $key => $value) {
            if (!\is_array($value)) {
                continue;
            }

            $settings[$key] = match ($key) {
                'dictionary',
                'nonSeparatorTokens',
                'separatorTokens',
                'sortableAttributes',
                'stopWords' => collect($value)->uniqueStrict()->sort(\SORT_STRING)->values()->all(),
                'displayedAttributes',
                'searchableAttributes' => collect($value)->uniqueStrict()->values()->all(),
                'synonyms'             => collect($value)->sortKeys(\SORT_STRING)->all(),
                'faceting'             => self::sortFaceting($value),
                'localizedAttributes'  => array_map(
                    fn (mixed $rule): mixed => \is_array($rule)
                        ? self::orderKeys($rule, ['attributePatterns', 'locales'])
                        : $rule,
                    $value,
                ),
                'pagination'    => self::orderKeys($value, ['maxTotalHits']),
                'typoTolerance' => self::sortTypoTolerance($value),
                default         => $value,
            };
        }

        return $settings;
    }

    /**
     * Get MeiliSearch engine version.
     */
    public static function engineVersion(): ?string
    {
        try {
            return resolve(Client::class)->version()['pkgVersion'] ?? null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Export value to a string.
     */
    public static function export(mixed $value): string
    {
        if (class_exists(VarExporter::class)) {
            return VarExporter::export($value, VarExporter::INLINE_SCALAR_LIST);
        }

        return var_export($value, true);
    }

    /**
     * Format a number of bytes, e.g. `1.5 MB`.
     */
    public static function formatBytes(float|int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $power = $bytes > 0 ? min((int) floor(log($bytes, 1024)), \count($units) - 1) : 0;
        $value = round($bytes / 1024 ** $power, $power === 0 ? 0 : $precision);

        return \sprintf('%s %s', $value, $units[$power]);
    }

    /**
     * Convert index data to table array.
     *
     * @param array<array-key, mixed> $data
     *
     * @return list<array{string, string}>
     */
    public static function convertIndexDataToTable(array $data): array
    {
        return array_map(
            fn (int|string $key, mixed $value): array => [Str::headline((string) $key), self::export($value)],
            array_keys($data),
            array_values($data),
        );
    }

    /**
     * Convert index changes to table array.
     *
     * @param array<string, array{old: mixed, new: mixed}> $changes
     *
     * @return list<array{string, string, string}>
     */
    public static function convertIndexChangesToTable(array $changes): array
    {
        return array_map(
            fn (int|string $key, array $value): array => [
                Str::headline((string) $key),
                self::export($value['old']),
                self::export($value['new']),
            ],
            array_keys($changes),
            array_values($changes),
        );
    }

    /**
     * Guess the model namespace using the configured paths.
     */
    public static function guessModelNamespace(string $model): string
    {
        /** @var array<string, string> $paths */
        $paths = config('meilitools.paths', []);

        return collect($paths)
            ->map(fn (string $namespace): string => $namespace . '\\')
            ->first(fn (string $namespace): bool => class_exists($namespace . $model)) . $model
        ;
    }

    /**
     * Determine if the given model uses soft deletes and soft deletes are enabled for Scout.
     *
     * @param class-string<Model>|Model $model
     */
    public static function usesSoftDelete(Model|string $model): bool
    {
        return config('scout.soft_delete', false) && \in_array(SoftDeletes::class, class_uses_recursive($model), true);
    }

    /**
     * Get the index settings configured for Scout's MeiliSearch driver.
     *
     * Entries without settings (e.g. `'index-settings' => [User::class]`) are normalized to empty settings.
     *
     * @return array<string, array<string, mixed>> Keyed by model class or index name.
     */
    public static function scoutIndexSettings(): array
    {
        $settings = [];
        foreach ((array) config('scout.meilisearch.index-settings', []) as $name => $value) {
            if (\is_array($value)) {
                $settings[(string) $name] = $value;
            } elseif (\is_string($value)) {
                $settings[$value] = [];
            }
        }

        return $settings;
    }

    /**
     * Get the searchable model classes configured in Scout's MeiliSearch index settings.
     *
     * @return list<class-string<Model>>
     */
    public static function scoutModels(): array
    {
        $models = [];
        foreach (array_keys(self::scoutIndexSettings()) as $name) {
            if (self::isSearchableModel($name)) {
                $models[] = $name;
            }
        }

        return $models;
    }

    /**
     * Get the settings configured in Scout's MeiliSearch index settings which aren't bound to a model.
     *
     * @return array<string, array<string, mixed>> Keyed by (prefixed) index name.
     */
    public static function scoutIndexes(): array
    {
        return collect(self::scoutIndexSettings())
            ->reject(fn (array $settings, string $name): bool => class_exists($name))
            ->mapWithKeys(fn (array $settings, string $name): array => [self::scoutIndexName($name) => $settings])
            ->all()
        ;
    }

    /**
     * Get the index name for a Scout configured index, applying the Scout prefix the same way Scout does.
     */
    public static function scoutIndexName(string $name): string
    {
        $prefix = (string) config('scout.prefix', '');

        return Str::startsWith($name, $prefix) ? $name : $prefix . $name;
    }

    /**
     * Get the index name of a searchable model.
     *
     * @param class-string<Model> $class
     *
     * @throws MeiliToolsException When the class isn't a searchable model.
     */
    public static function modelIndexName(string $class): string
    {
        $model = resolve($class);
        if (!$model instanceof Model || !method_exists($model, 'searchableAs')) {
            throw new MeiliToolsException(\sprintf("Class '%s' is not a searchable model", $class));
        }

        return $model->searchableAs();
    }

    /**
     * Whether the given class is a searchable Eloquent model.
     *
     * @phpstan-assert-if-true class-string<Model> $class
     */
    public static function isSearchableModel(string $class): bool
    {
        return class_exists($class)
            && is_a($class, Model::class, true)
            && \in_array(Searchable::class, class_uses_recursive($class), true);
    }

    /**
     * Order the given keys first, in the given order, keeping any unknown keys last.
     *
     * @param array<array-key, mixed> $value
     * @param list<string>            $keys
     *
     * @return array<array-key, mixed>
     */
    protected static function orderKeys(array $value, array $keys): array
    {
        return array_replace(array_intersect_key(array_fill_keys($keys, null), $value), $value);
    }

    /**
     * Sort faceting settings.
     *
     * MeiliSearch always keeps a '*' entry when sorting facet values.
     *
     * @param array<array-key, mixed> $value
     *
     * @return array<array-key, mixed>
     */
    protected static function sortFaceting(array $value): array
    {
        $value = self::orderKeys($value, ['maxValuesPerFacet', 'sortFacetValuesBy']);
        if (isset($value['sortFacetValuesBy']) && \is_array($value['sortFacetValuesBy'])) {
            $value['sortFacetValuesBy'] = collect($value['sortFacetValuesBy'] + ['*' => 'alpha'])
                ->sortKeys(\SORT_STRING)
                ->all()
            ;
        }

        return $value;
    }

    /**
     * Sort typo tolerance settings.
     *
     * @param array<array-key, mixed> $value
     *
     * @return array<array-key, mixed>
     */
    protected static function sortTypoTolerance(array $value): array
    {
        $value = self::orderKeys(
            $value,
            ['enabled', 'minWordSizeForTypos', 'disableOnWords', 'disableOnAttributes', 'disableOnNumbers'],
        );
        if (isset($value['minWordSizeForTypos']) && \is_array($value['minWordSizeForTypos'])) {
            $value['minWordSizeForTypos'] = self::orderKeys($value['minWordSizeForTypos'], ['oneTypo', 'twoTypos']);
        }
        foreach (['disableOnWords', 'disableOnAttributes'] as $key) {
            if (isset($value[$key]) && \is_array($value[$key])) {
                $value[$key] = collect($value[$key])->uniqueStrict()->sort(\SORT_STRING)->values()->all();
            }
        }

        return $value;
    }
}
