<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools;

use Brick\VarExporter\VarExporter;

class Helpers
{
    /**
     * Default MeiliSearch index settings.
     *
     * @var array
     */
    public const DEFAULT_SETTINGS = [
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
