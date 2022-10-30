<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools;

use Brick\VarExporter\VarExporter;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Scout\EngineManager;
use MeiliSearch\MeiliSearch;
use Throwable;

class Helpers
{
    /**
     * Whether Scout is using the MeiliSearch driver.
     *
     * @return bool
     */
    public static function usingMeiliSearch(): bool
    {
        return app(EngineManager::class)->getDefaultDriver() === 'meilisearch';
    }

    /**
     * Throw exception unless Scout is using the MeiliSearch driver.
     *
     * @throws \Dwarf\MeiliTools\Exceptions\MeiliToolsException
     *
     * @return void
     */
    public static function throwUnlessMeiliSearch(): void
    {
        throw_unless(
            self::usingMeiliSearch(),
            MeiliToolsException::class,
            'Scout must be using the MeiliSearch driver'
        );
    }

    /**
     * Default MeiliSearch index settings.
     *
     * @param string|null $version MeiliSearch engine version.
     *
     * @return array
     */
    public static function defaultSettings(?string $version = null): array
    {
        $settings = [
            'displayedAttributes'  => ['*'],
            'distinctAttribute'    => null,
            'filterableAttributes' => [],
            'rankingRules'         => ['words', 'typo', 'proximity', 'attribute', 'sort', 'exactness'],
            'searchableAttributes' => ['*'],
            'sortableAttributes'   => [],
            'stopWords'            => [],
            'synonyms'             => [],
            'typoTolerance'        => [],
        ];

        // Add actual typo tolerance defaults for engine version >=0.27.0.
        if ($version && version_compare($version, '0.27.0', '>=')) {
            $settings['typoTolerance'] = [
                'enabled'             => true,
                'minWordSizeForTypos' => [
                    'oneTypo'  => 5,
                    'twoTypos' => 9,
                ],
                'disableOnWords'      => [],
                'disableOnAttributes' => [],
            ];
        }

        // Add faceting and pagination defaults for version >=0.28.0.
        if ($version && version_compare($version, '0.28.0', '>=')) {
            $settings['faceting'] = ['maxValuesPerFacet' => 100];
            $settings['pagination'] = ['maxTotalHits' => 1000];
        }

        // Sort settings by key.
        ksort($settings);

        return $settings;
    }

    /**
     * Sort MeiliSearch settings.
     *
     * Certain settings are automatically sorted by MeiliSearch,
     * so we do it the same way to correctly compare data.
     *
     * @param array $settings Settings.
     *
     * @return array
     */
    public static function sortSettings(array $settings): array
    {
        $sorter = function (&$value, $key) {
            if (\is_array($value)) {
                if (\in_array($key, ['filterableAttributes', 'stopWords', 'sortableAttributes'], true)) {
                    sort($value);
                }
                if ($key === 'synonyms') {
                    ksort($value);
                    array_walk($value, fn (&$list) => sort($list));
                }
                if ($key === 'faceting') {
                    $value = array_replace(Arr::only(['maxValuesPerFacet' => null], array_keys($value)), $value);
                }
                if ($key === 'pagination') {
                    $value = array_replace(Arr::only(['maxTotalHits' => null], array_keys($value)), $value);
                }
                if ($key === 'typoTolerance') {
                    $value = array_replace(
                        Arr::only(
                            [
                                'enabled'             => null,
                                'minWordSizeForTypos' => null,
                                'disableOnWords'      => null,
                                'disableOnAttributes' => null,
                            ],
                            array_keys($value)
                        ),
                        $value
                    );
                    if (isset($value['minWordSizeForTypos'])) {
                        ksort($value['minWordSizeForTypos']);
                    }
                    if (isset($value['disableOnWords'])) {
                        sort($value['disableOnWords']);
                    }
                    if (isset($value['disableOnAttributes'])) {
                        sort($value['disableOnAttributes']);
                    }
                }
            }
        };
        ksort($settings);
        array_walk($settings, $sorter);

        return $settings;
    }

    /**
     * Get MeiliSearch engine version.
     *
     * @return string|null
     */
    public static function engineVersion(): ?string
    {
        $version = null;

        try {
            $version = app(EngineManager::class)->engine()->version()['pkgVersion'] ?? null;
        } catch (Throwable $e) {
            // Silently ignore.
        }

        return $version;
    }

    /**
     * Export value to a string.
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function export($value): string
    {
        if (class_exists(VarExporter::class)) {
            return VarExporter::export($value, VarExporter::INLINE_NUMERIC_SCALAR_ARRAY);
        }

        return var_export($value, true);
    }

    /**
     * Convert index data to table array.
     *
     * @param array $data Key / value array.
     *
     * @return array
     */
    public static function convertIndexDataToTable(array $data): array
    {
        return collect($data)
            ->map(function ($value, $key) {
                return [
                    (string) Str::of($key)->snake()->replace('_', ' ')->title(),
                    self::export($value),
                ];
            })
            ->values()
            ->all()
        ;
    }

    /**
     * Convert index changes to table array.
     *
     * @param array $changes Key / value array.
     *
     * @return array
     */
    public static function convertIndexChangesToTable(array $changes): array
    {
        return collect($changes)
            ->map(function ($value, $key) {
                return [
                    (string) Str::of($key)->snake()->replace('_', ' ')->title(),
                    self::export($value['old']),
                    self::export($value['new']),
                ];
            })
            ->values()
            ->all()
        ;
    }

    /**
     * Guess the model namespace using the configured paths.
     *
     * @param string $model Name of the model.
     *
     * @return string
     */
    public static function guessModelNamespace(string $model): string
    {
        return collect(config('meilitools.paths'))
            ->map(fn (string $path) => $path . '\\')
            ->first(fn (string $path) => class_exists($path . $model)) . $model;
    }
}
