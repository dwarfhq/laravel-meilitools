<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools;

use Brick\VarExporter\VarExporter;
use MeiliSearch\MeiliSearch;

class Helpers
{
    /**
     * Default MeiliSearch index settings.
     *
     * @return array
     */
    public static function defaultSettings(): array
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
        ];

        // Add typo tolerance to default settings for version >=0.23.2.
        if (version_compare(MeiliSearch::VERSION, '0.23.2', '>=')) {
            $settings['typoTolerance'] = [];
        }

        return $settings;
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
}
