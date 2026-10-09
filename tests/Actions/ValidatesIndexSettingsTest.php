<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings;
use Dwarf\MeiliTools\Contracts\Rules\ArrayAssocRule;
use Dwarf\MeiliTools\Rules\ArrayAssoc;
use Dwarf\MeiliTools\Tests\Tools;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Get the associative array validation message for an attribute.
 */
function assocMessage(string $attribute): string
{
    return Str::replace(':attribute', $attribute, ArrayAssoc::MESSAGE);
}

/**
 * Get the readable attribute name of a field.
 */
function attributeName(string $field): string
{
    return Str::of($field)->headline()->replace('.', ' ')->lower()->toString();
}

/**
 * Test ValidatesIndexSettings::rules() method.
 */
test('rules', function (): void {
    $assoc = resolve(ArrayAssocRule::class);
    $list = ['sometimes', 'nullable', 'list'];
    $string = ['required', 'string'];

    $expected = [
        'dictionary'                                => $list,
        'dictionary.*'                              => $string,
        'displayedAttributes'                       => [...$list, 'min:1'],
        'displayedAttributes.*'                     => $string,
        'distinctAttribute'                         => ['sometimes', 'nullable', 'string'],
        'facetSearch'                               => ['sometimes', 'nullable', 'boolean:strict'],
        'faceting'                                  => ['sometimes', 'nullable', $assoc],
        'faceting.maxValuesPerFacet'                => ['sometimes', 'nullable', 'integer:strict', 'min:0'],
        'faceting.sortFacetValuesBy'                => ['sometimes', 'nullable', $assoc],
        'faceting.sortFacetValuesBy.*'              => ['required', Rule::in(['alpha', 'count'])],
        'filterableAttributes'                      => $list,
        'localizedAttributes'                       => $list,
        'localizedAttributes.*'                     => ['required', $assoc],
        'localizedAttributes.*.attributePatterns'   => ['required', 'list', 'min:1'],
        'localizedAttributes.*.attributePatterns.*' => $string,
        'localizedAttributes.*.locales'             => ['present', 'list'],
        'localizedAttributes.*.locales.*'           => $string,
        'nonSeparatorTokens'                        => $list,
        'nonSeparatorTokens.*'                      => $string,
        'pagination'                                => ['sometimes', 'nullable', $assoc],
        'pagination.maxTotalHits'                   => ['sometimes', 'nullable', 'integer:strict', 'min:0'],
        'prefixSearch'                              => [
            'sometimes',
            'nullable',
            Rule::in(['indexingTime', 'disabled']),
        ],
        'proximityPrecision' => [
            'sometimes',
            'nullable',
            Rule::in(['byWord', 'byAttribute']),
        ],
        'rankingRules'                               => [...$list, 'min:1'],
        'rankingRules.*'                             => $string,
        'searchCutoffMs'                             => ['sometimes', 'nullable', 'integer:strict', 'min:0'],
        'searchableAttributes'                       => [...$list, 'min:1'],
        'searchableAttributes.*'                     => $string,
        'separatorTokens'                            => $list,
        'separatorTokens.*'                          => $string,
        'sortableAttributes'                         => $list,
        'sortableAttributes.*'                       => $string,
        'stopWords'                                  => $list,
        'stopWords.*'                                => $string,
        'synonyms'                                   => ['sometimes', 'nullable', $assoc],
        'synonyms.*'                                 => ['required', 'list'],
        'synonyms.*.*'                               => $string,
        'typoTolerance'                              => ['sometimes', 'nullable', $assoc],
        'typoTolerance.enabled'                      => ['sometimes', 'nullable', 'boolean:strict'],
        'typoTolerance.minWordSizeForTypos'          => ['sometimes', 'nullable', $assoc],
        'typoTolerance.minWordSizeForTypos.oneTypo'  => ['sometimes', 'nullable', 'integer:strict', 'between:0,255'],
        'typoTolerance.minWordSizeForTypos.twoTypos' => ['sometimes', 'nullable', 'integer:strict', 'between:0,255'],
        'typoTolerance.disableOnWords'               => $list,
        'typoTolerance.disableOnWords.*'             => $string,
        'typoTolerance.disableOnAttributes'          => $list,
        'typoTolerance.disableOnAttributes.*'        => $string,
        'typoTolerance.disableOnNumbers'             => ['sometimes', 'nullable', 'boolean:strict'],
    ];

    $rules = resolve(ValidatesIndexSettings::class)->rules();

    expect(Arr::except($rules, 'filterableAttributes.*'))->toEqual($expected)
        ->and($rules['filterableAttributes.*'][0])->toBe('required')
        ->and($rules['filterableAttributes.*'][1])->toBeInstanceOf(Closure::class)
    ;
});

/**
 * Test ValidatesIndexSettings::passes() method.
 */
test('passes', function (callable $data): void {
    $action = resolve(ValidatesIndexSettings::class);

    [$value, $validated, $passes, $messages] = $data();

    expect($action->passes($value))->toBe($passes)
        ->and($action->validated())->toBe($validated)
        ->and($action->messages())->toBe($messages)
    ;
})->with('passesProvider');

/**
 * Test ValidatesIndexSettings::passes() method with typo tolerance.
 */
test('passes typo tolerance', function (callable $data): void {
    $action = resolve(ValidatesIndexSettings::class);

    [$value, $validated, $passes, $messages] = $data();

    expect($action->passes($value))->toBe($passes)
        ->and($action->validated())->toBe($validated)
        ->and($action->messages())->toBe($messages)
    ;
})->with('passesTypoToleranceProvider');

/**
 * Test ValidatesIndexSettings::validate() method.
 */
test('validate', function (): void {
    $action = resolve(ValidatesIndexSettings::class);

    expect($action->validate(Tools::advancedSettings()))->toEqual(Tools::advancedSettings())
        ->and(fn () => $action->validate(['distinctAttribute' => 42]))->toThrow(ValidationException::class)
    ;
});

/**
 * Data provider for ValidatesIndexSettings::passes().
 *
 * Using yield for better overview, and closures so Laravel facades work during tests.
 */
dataset('passesProvider', function () {
    $settings = Tools::movieSettings() + Tools::advancedSettings();

    $lists = [
        'dictionary',
        'displayedAttributes',
        'filterableAttributes',
        'nonSeparatorTokens',
        'rankingRules',
        'searchableAttributes',
        'separatorTokens',
        'sortableAttributes',
        'stopWords',
    ];

    yield 'empty array' => [fn (): array => [[], [], true, []]];

    foreach ($settings as $field => $value) {
        $name = attributeName($field);

        yield "{$name} valid" => [fn (): array => [[$field => $value], [$field => $value], true, []]];

        yield "{$name} null" => [fn (): array => [[$field => null], [$field => null], true, []]];
    }

    foreach ($lists as $field) {
        $name = attributeName($field);

        yield "{$name} not list" => [fn (): array => [
            [$field => 42],
            null,
            false,
            [$field => [__('validation.list', ['attribute' => $name])]],
        ]];

        yield "{$name} assoc not list" => [fn (): array => [
            [$field => ['foo' => 'bar']],
            null,
            false,
            [$field => [__('validation.list', ['attribute' => $name])]],
        ]];

        if (in_array($field, ['displayedAttributes', 'rankingRules', 'searchableAttributes'], true)) {
            yield "{$name} empty error" => [fn (): array => [
                [$field => []],
                null,
                false,
                [$field => [__('validation.min.array', ['attribute' => $name, 'min' => 1])]],
            ]];
        }

        yield "{$name} required error" => [fn (): array => [
            [$field => [null]],
            null,
            false,
            [$field . '.0' => [__('validation.required', ['attribute' => $field . '.0'])]],
        ]];

        yield "{$name} string error" => [fn (): array => [
            [$field => [42]],
            null,
            false,
            [$field . '.0' => [__('validation.string', ['attribute' => $field . '.0'])]],
        ]];
    }

    $granular = [
        'genre',
        ['attributePatterns' => ['year'], 'features' => ['facetSearch' => true, 'filter' => ['comparison' => true]]],
        ['attributePatterns' => ['title', 'author']],
    ];

    yield 'granular filterable attributes valid' => [fn (): array => [
        ['filterableAttributes' => $granular],
        ['filterableAttributes' => $granular],
        true,
        [],
    ]];

    yield 'granular filterable attributes patterns error' => [fn (): array => [
        ['filterableAttributes' => [['features' => ['facetSearch' => true]]]],
        null,
        false,
        ['filterableAttributes.0' => [
            __('validation.required', ['attribute' => 'filterableAttributes.0.attributePatterns']),
        ]],
    ]];

    yield 'granular filterable attributes boolean error' => [fn (): array => [
        ['filterableAttributes' => [['attributePatterns' => ['year'], 'features' => ['filter' => ['equality' => 1]]]]],
        null,
        false,
        ['filterableAttributes.0' => [
            __('validation.boolean', ['attribute' => 'filterableAttributes.0.features.filter.equality']),
        ]],
    ]];

    yield 'distinct attribute string error' => [fn (): array => [
        ['distinctAttribute' => 42],
        null,
        false,
        ['distinctAttribute' => [__('validation.string', ['attribute' => 'distinct attribute'])]],
    ]];

    yield 'facet search boolean error' => [fn (): array => [
        ['facetSearch' => 1],
        null,
        false,
        ['facetSearch' => [__('validation.boolean', ['attribute' => 'facet search'])]],
    ]];

    yield 'search cutoff ms integer error' => [fn (): array => [
        ['searchCutoffMs' => '150'],
        null,
        false,
        ['searchCutoffMs' => [__('validation.integer', ['attribute' => 'search cutoff ms'])]],
    ]];

    yield 'search cutoff ms min error' => [fn (): array => [
        ['searchCutoffMs' => -1],
        null,
        false,
        ['searchCutoffMs' => [__('validation.min.numeric', ['attribute' => 'search cutoff ms', 'min' => 0])]],
    ]];

    yield 'prefix search in error' => [fn (): array => [
        ['prefixSearch' => 'foo'],
        null,
        false,
        ['prefixSearch' => [__('validation.in', ['attribute' => 'prefix search'])]],
    ]];

    yield 'proximity precision in error' => [fn (): array => [
        ['proximityPrecision' => 'foo'],
        null,
        false,
        ['proximityPrecision' => [__('validation.in', ['attribute' => 'proximity precision'])]],
    ]];

    foreach (['faceting', 'pagination', 'synonyms', 'typoTolerance'] as $field) {
        $name = attributeName($field);

        yield "{$name} not array nor assoc" => [fn (): array => [
            [$field => 42],
            null,
            false,
            [$field => [assocMessage($name)]],
        ]];
    }

    yield 'faceting max values per facet integer error' => [fn (): array => [
        ['faceting' => ['maxValuesPerFacet' => '10']],
        null,
        false,
        ['faceting.maxValuesPerFacet' => [__('validation.integer', ['attribute' => 'faceting max values per facet'])]],
    ]];

    yield 'faceting sort facet values by not assoc' => [fn (): array => [
        ['faceting' => ['sortFacetValuesBy' => ['count']]],
        null,
        false,
        ['faceting.sortFacetValuesBy' => [assocMessage('faceting sort facet values by')]],
    ]];

    yield 'faceting sort facet values by in error' => [fn (): array => [
        ['faceting' => ['sortFacetValuesBy' => ['*' => 'foo']]],
        null,
        false,
        ['faceting.sortFacetValuesBy.*' => [__('validation.in', ['attribute' => 'faceting.sortFacetValuesBy.*'])]],
    ]];

    yield 'pagination max total hits integer error' => [fn (): array => [
        ['pagination' => ['maxTotalHits' => 'foo']],
        null,
        false,
        ['pagination.maxTotalHits' => [__('validation.integer', ['attribute' => 'pagination max total hits'])]],
    ]];

    yield 'localized attributes rule not assoc' => [fn (): array => [
        ['localizedAttributes' => [['foo']]],
        null,
        false,
        [
            'localizedAttributes.0'                   => [assocMessage('localizedAttributes.0')],
            'localizedAttributes.0.attributePatterns' => [
                __('validation.required', ['attribute' => 'localizedAttributes.0.attributePatterns']),
            ],
            'localizedAttributes.0.locales' => [
                __('validation.present', ['attribute' => 'localizedAttributes.0.locales']),
            ],
        ],
    ]];

    yield 'localized attributes missing fields' => [fn (): array => [
        ['localizedAttributes' => [['attributePatterns' => []]]],
        null,
        false,
        [
            'localizedAttributes.0.attributePatterns' => [
                __('validation.required', ['attribute' => 'localizedAttributes.0.attributePatterns']),
            ],
            'localizedAttributes.0.locales' => [
                __('validation.present', ['attribute' => 'localizedAttributes.0.locales']),
            ],
        ],
    ]];

    yield 'localized attributes empty locales' => [fn (): array => [
        ['localizedAttributes' => [['attributePatterns' => ['*'], 'locales' => []]]],
        ['localizedAttributes' => [['attributePatterns' => ['*'], 'locales' => []]]],
        true,
        [],
    ]];

    yield 'synonyms array not assoc' => [fn (): array => [
        ['synonyms' => [42]],
        null,
        false,
        [
            'synonyms'   => [assocMessage('synonyms')],
            'synonyms.0' => [__('validation.list', ['attribute' => 'synonyms.0'])],
        ],
    ]];

    yield 'synonyms foo required error' => [fn (): array => [
        ['synonyms' => ['foo' => null]],
        null,
        false,
        ['synonyms.foo' => [__('validation.required', ['attribute' => 'synonyms.foo'])]],
    ]];

    yield 'synonyms foo list error' => [fn (): array => [
        ['synonyms' => ['foo' => 42]],
        null,
        false,
        ['synonyms.foo' => [__('validation.list', ['attribute' => 'synonyms.foo'])]],
    ]];

    yield 'synonyms foo zero required error' => [fn (): array => [
        ['synonyms' => ['foo' => [null]]],
        null,
        false,
        ['synonyms.foo.0' => [__('validation.required', ['attribute' => 'synonyms.foo.0'])]],
    ]];

    yield 'synonyms foo zero string error' => [fn (): array => [
        ['synonyms' => ['foo' => [42]]],
        null,
        false,
        ['synonyms.foo.0' => [__('validation.string', ['attribute' => 'synonyms.foo.0'])]],
    ]];

    yield 'typo tolerance array not assoc' => [fn (): array => [
        ['typoTolerance' => [42]],
        null,
        false,
        ['typoTolerance' => [assocMessage('typo tolerance')]],
    ]];
});

/**
 * Data provider for ValidatesIndexSettings::passes() with typo tolerance.
 *
 * Using yield for better overview, and closures so Laravel facades work during tests.
 */
dataset('passesTypoToleranceProvider', function () {
    $field = 'typoTolerance';
    $name = attributeName($field);

    $settings = [
        'enabled'             => true,
        'minWordSizeForTypos' => [
            'oneTypo'  => 2,
            'twoTypos' => 2,
        ],
        'disableOnWords'      => ['foo', 'bar'],
        'disableOnAttributes' => ['foo', 'bar'],
        'disableOnNumbers'    => true,
    ];

    yield "{$name} valid" => [fn (): array => [[$field => $settings], [$field => $settings], true, []]];

    yield "{$name} null" => [fn (): array => [[$field => null], [$field => null], true, []]];

    foreach (array_keys($settings) as $prop) {
        $name = attributeName("{$field}.{$prop}");

        yield "{$name} null" => [fn (): array => [
            [$field => [$prop => null]],
            [$field => [$prop => null]],
            true,
            [],
        ]];

        if ($prop === 'minWordSizeForTypos') {
            foreach (['oneTypo', 'twoTypos'] as $size) {
                $name = attributeName("{$field}.{$prop}.{$size}");

                yield "{$name} null" => [fn (): array => [
                    [$field => [$prop => [$size => null]]],
                    [$field => [$prop => [$size => null]]],
                    true,
                    [],
                ]];
            }
        }
    }

    foreach (['enabled', 'disableOnNumbers'] as $prop) {
        $name = attributeName("{$field}.{$prop}");

        foreach ([42, 1, '1'] as $value) {
            yield "{$name} boolean error " . var_export($value, true) => [fn (): array => [
                [$field => [$prop => $value]],
                null,
                false,
                ["{$field}.{$prop}" => [__('validation.boolean', ['attribute' => $name])]],
            ]];
        }
    }

    $prop = 'minWordSizeForTypos';
    $name = attributeName("{$field}.{$prop}");

    yield "{$name} not array nor assoc" => [fn (): array => [
        [$field => [$prop => 42]],
        null,
        false,
        ["{$field}.{$prop}" => [assocMessage($name)]],
    ]];

    yield "{$name} array not assoc" => [fn (): array => [
        [$field => [$prop => [42]]],
        null,
        false,
        ["{$field}.{$prop}" => [assocMessage($name)]],
    ]];

    foreach (['oneTypo', 'twoTypos'] as $size) {
        $name = attributeName("{$field}.{$prop}.{$size}");

        foreach (['foo', '4'] as $value) {
            yield "{$name} integer error {$value}" => [fn (): array => [
                [$field => [$prop => [$size => $value]]],
                null,
                false,
                ["{$field}.{$prop}.{$size}" => [__('validation.integer', ['attribute' => $name])]],
            ]];
        }

        yield "{$name} between error low" => [fn (): array => [
            [$field => [$prop => [$size => -1]]],
            null,
            false,
            ["{$field}.{$prop}.{$size}" => [
                __('validation.between.numeric', ['attribute' => $name, 'min' => 0, 'max' => 255]),
            ]],
        ]];

        yield "{$name} between error high" => [fn (): array => [
            [$field => [$prop => [$size => 300]]],
            null,
            false,
            ["{$field}.{$prop}.{$size}" => [
                __('validation.between.numeric', ['attribute' => $name, 'min' => 0, 'max' => 255]),
            ]],
        ]];
    }

    foreach (['disableOnWords', 'disableOnAttributes'] as $prop) {
        $name = attributeName("{$field}.{$prop}");

        yield "{$name} not list" => [fn (): array => [
            [$field => [$prop => 42]],
            null,
            false,
            ["{$field}.{$prop}" => [__('validation.list', ['attribute' => $name])]],
        ]];

        yield "{$name} required error" => [fn (): array => [
            [$field => [$prop => [null]]],
            null,
            false,
            ["{$field}.{$prop}.0" => [__('validation.required', ['attribute' => "{$field}.{$prop}.0"])]],
        ]];

        yield "{$name} string error" => [fn (): array => [
            [$field => [$prop => [42]]],
            null,
            false,
            ["{$field}.{$prop}.0" => [__('validation.string', ['attribute' => "{$field}.{$prop}.0"])]],
        ]];
    }
});
