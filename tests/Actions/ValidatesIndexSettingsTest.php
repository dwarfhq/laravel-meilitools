<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings;
use Dwarf\MeiliTools\Contracts\Rules\ArrayAssocRule;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\TestCase;
use Dwarf\MeiliTools\Tests\Tools;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use MeiliSearch\MeiliSearch;

/**
 * @internal
 */
class ValidatesIndexSettingsTest extends TestCase
{
    /**
     * Test ValidatesIndexSettings::rules() method.
     *
     * @return void
     */
    public function testRules(): void
    {
        $action = $this->app->make(ValidatesIndexSettings::class);
        $version = Helpers::engineVersion() ?: '0.0.0';

        $actual = $action->rules($version);
        $expected = [
            'displayedAttributes'    => ['sometimes', 'nullable', 'array', 'min:1'],
            'displayedAttributes.*'  => ['required', 'string'],
            'distinctAttribute'      => ['sometimes', 'nullable', 'string'],
            'filterableAttributes'   => ['sometimes', 'nullable', 'array'],
            'filterableAttributes.*' => ['required', 'string'],
            'rankingRules'           => ['sometimes', 'nullable', 'array', 'min:1'],
            'rankingRules.*'         => ['required', 'string'],
            'searchableAttributes'   => ['sometimes', 'nullable', 'array', 'min:1'],
            'searchableAttributes.*' => ['required', 'string'],
            'sortableAttributes'     => ['sometimes', 'nullable', 'array'],
            'sortableAttributes.*'   => ['required', 'string'],
            'stopWords'              => ['sometimes', 'nullable', 'array'],
            'stopWords.*'            => ['required', 'string'],
            'synonyms'               => ['sometimes', 'nullable', $this->app->make(ArrayAssocRule::class)],
            'synonyms.*'             => ['required', 'array'],
            'synonyms.*.*'           => ['required', 'string'],
        ];
        // Add typo tolerance to validation rules for version >=0.23.2.
        if (version_compare(MeiliSearch::VERSION, '0.23.2', '>=')) {
            $expected['typoTolerance'] = ['sometimes', 'nullable', $this->app->make(ArrayAssocRule::class)];
        }

        // Add actual typo tolerance validation rules for engine version >=0.27.0.
        if (version_compare($version, '0.27.0', '>=')) {
            $expected['typoTolerance.enabled'] = ['sometimes', 'nullable', 'boolean'];
            $expected['typoTolerance.minWordSizeForTypos'] = [
                'sometimes',
                'nullable',
                $this->app->make(ArrayAssocRule::class),
            ];
            $expected['typoTolerance.minWordSizeForTypos.oneTypo'] = [
                'sometimes',
                'nullable',
                'integer',
                'between:0,255',
            ];
            $expected['typoTolerance.minWordSizeForTypos.twoTypos'] = [
                'sometimes',
                'nullable',
                'integer',
                'between:0,255',
            ];
            $expected['typoTolerance.disableOnWords'] = ['sometimes', 'nullable', 'array'];
            $expected['typoTolerance.disableOnWords.*'] = ['required', 'string'];
            $expected['typoTolerance.disableOnAttributes'] = ['sometimes', 'nullable', 'array'];
            $expected['typoTolerance.disableOnAttributes.*'] = ['required', 'string'];
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test ValidatesIndexSettings::passes() method.
     *
     * @dataProvider passesProvider
     *
     * @param callable $data Callable with test data.
     *
     * @return void
     */
    public function testPasses(callable $data): void
    {
        $action = $this->app->make(ValidatesIndexSettings::class);

        [$value, $validated, $passes, $messages] = $data();

        $actualPasses = $action->passes($value, Helpers::engineVersion());
        $this->assertSame($passes, $actualPasses);

        $actualValidated = $action->validated();
        $this->assertSame($validated, $actualValidated);

        $actualMessages = $action->messages();
        $this->assertSame($messages, $actualMessages);
    }

    /**
     * Test ValidatesIndexSettings::passes() method with typo tolerance.
     *
     * @dataProvider passesTypoToleranceProvider
     *
     * @param callable $data Callable with test data.
     *
     * @return void
     */
    public function testPassesTypoTolerance(callable $data): void
    {
        // Check if test should be run on this engine version.
        $version = Helpers::engineVersion() ?: '0.0.0';
        if (version_compare(MeiliSearch::VERSION, '0.23.2', '<') || version_compare($version, '0.27.0', '<')) {
            $this->markTestSkipped('Typo tolerance is only available from 0.27.0 and up.');
        }

        $action = $this->app->make(ValidatesIndexSettings::class);

        [$value, $validated, $passes, $messages] = $data();

        $actualPasses = $action->passes($value, $version);
        $this->assertSame($passes, $actualPasses);

        $actualValidated = $action->validated();
        $this->assertSame($validated, $actualValidated);

        $actualMessages = $action->messages();
        $this->assertSame($messages, $actualMessages);
    }

    /**
     * Data provider for ValidateStylesAction::passes().
     *
     * Using yield for better overview, and closures so Laravel facades work during tests.
     *
     * @return iterable
     */
    public function passesProvider(): iterable
    {
        $settings = Tools::movieSettings();

        $fields = [
            'displayedAttributes',
            'filterableAttributes',
            'rankingRules',
            'searchableAttributes',
            'sortableAttributes',
            'stopWords',
        ];

        // Test successful validation of all fields.

        yield 'empty array' => [fn () => [[], [], true, []]];

        foreach ($settings as $field => $value) {
            $name = Str::of($field)->headline()->lower();

            yield "{$name} valid" => [fn () => [
                [$field => $settings[$field]],
                [$field => $settings[$field]],
                true,
                [],
            ]];

            yield "{$name} null" => [fn () => [[$field => null], [$field => null], true, []]];
        }

        // Test unsuccessful validation of all fields.

        foreach ($fields as $field) {
            $name = Str::of($field)->headline()->lower();

            yield "{$name} not array" => [fn () => [
                [$field => 42],
                null,
                false,
                [
                    $field => [
                        __('validation.array', ['attribute' => $name]),
                    ],
                ],
            ]];

            if (\in_array($field, ['displayedAttributes', 'rankingRules', 'searchableAttributes'])) {
                yield "{$name} empty error" => [fn () => [
                    [$field => []],
                    null,
                    false,
                    [
                        $field => [
                            __('validation.min.array', ['attribute' => $name, 'min' => 1]),
                        ],
                    ],
                ]];
            }

            yield "{$name} required error" => [fn () => [
                [$field => [null]],
                null,
                false,
                [
                    $field . '.0' => [
                        __('validation.required', ['attribute' => $field . '.0']),
                    ],
                ],
            ]];

            yield "{$name} string error" => [fn () => [
                [$field => [42]],
                null,
                false,
                [
                    $field . '.0' => [
                        __('validation.string', ['attribute' => $field . '.0']),
                    ],
                ],
            ]];
        }

        yield 'distinct attribute string error' => [fn () => [
            ['distinctAttribute' => 42],
            null,
            false,
            [
                'distinctAttribute' => [
                    __('validation.string', ['attribute' => 'distinct attribute']),
                ],
            ],
        ]];

        yield 'synonyms not array nor assoc' => [fn () => [
            ['synonyms' => 42],
            null,
            false,
            [
                'synonyms' => [
                    Str::replace(':attribute', 'synonyms', App::make(ArrayAssocRule::class)->message()),
                ],
            ],
        ]];

        yield 'synonyms array not assoc' => [fn () => [
            ['synonyms' => [42]],
            null,
            false,
            [
                'synonyms' => [
                    Str::replace(':attribute', 'synonyms', App::make(ArrayAssocRule::class)->message()),
                ],
                'synonyms.0' => [
                    __('validation.array', ['attribute' => 'synonyms.0']),
                ],
            ],
        ]];

        yield 'synonyms foo required error' => [fn () => [
            ['synonyms' => ['foo' => null]],
            null,
            false,
            [
                'synonyms.foo' => [
                    __('validation.required', ['attribute' => 'synonyms.foo']),
                ],
            ],
        ]];

        yield 'synonyms foo array error' => [fn () => [
            ['synonyms' => ['foo' => 42]],
            null,
            false,
            [
                'synonyms.foo' => [
                    __('validation.array', ['attribute' => 'synonyms.foo']),
                ],
            ],
        ]];

        yield 'synonyms foo zero required error' => [fn () => [
            ['synonyms' => ['foo' => [null]]],
            null,
            false,
            [
                'synonyms.foo.0' => [
                    __('validation.required', ['attribute' => 'synonyms.foo.0']),
                ],
            ],
        ]];

        yield 'synonyms foo zero string error' => [fn () => [
            ['synonyms' => ['foo' => [42]]],
            null,
            false,
            [
                'synonyms.foo.0' => [
                    __('validation.string', ['attribute' => 'synonyms.foo.0']),
                ],
            ],
        ]];

        if (version_compare(MeiliSearch::VERSION, '0.23.2', '>=')) {
            yield 'typo tolerance not array nor assoc' => [fn () => [
                ['typoTolerance' => 42],
                null,
                false,
                [
                    'typoTolerance' => [
                        Str::replace(':attribute', 'typo tolerance', App::make(ArrayAssocRule::class)->message()),
                    ],
                ],
            ]];

            yield 'typo tolerance array not assoc' => [fn () => [
                ['typoTolerance' => [42]],
                null,
                false,
                [
                    'typoTolerance' => [
                        Str::replace(':attribute', 'typo tolerance', App::make(ArrayAssocRule::class)->message()),
                    ],
                ],
            ]];
        }
    }

    /**
     * Data provider for ValidateStylesAction::passes().
     *
     * Using yield for better overview, and closures so Laravel facades work during tests.
     *
     * @return iterable
     */
    public function passesTypoToleranceProvider(): iterable
    {
        $field = 'typoTolerance';
        $name = Str::of($field)->headline()->lower();

        $props = ['enabled', 'minWordSizeForTypos', 'disableOnWords', 'disableOnAttributes'];

        $settings = [
            'enabled'             => true,
            'minWordSizeForTypos' => [
                'oneTypo'  => 2,
                'twoTypos' => 2,
            ],
            'disableOnWords'      => ['foo', 'bar'],
            'disableOnAttributes' => ['foo', 'bar'],
        ];

        yield "{$name} valid" => [fn () => [[$field => $settings], [$field => $settings], true, []]];

        yield "{$name} null" => [fn () => [[$field => null], [$field => null], true, []]];

        foreach (array_keys($settings) as $prop) {
            $name = Str::of("{$field}.{$prop}")->headline()->replace('.', ' ')->lower();

            yield "{$name} null" => [fn () => [
                [$field => [$prop => null]],
                [$field => [$prop => null]],
                true,
                [],
            ]];

            if ($prop === 'minWordSizeForTypos') {
                foreach (['oneTypo', 'twoTypos'] as $size) {
                    $name = Str::of("{$field}.{$prop}.{$size}")->headline()->replace('.', ' ')->lower();

                    yield "{$name} null" => [fn () => [
                        [$field => [$prop => [$size => null]]],
                        [$field => [$prop => [$size => null]]],
                        true,
                        [],
                    ]];
                }
            }
        }

        $prop = 'enabled';
        $name = Str::of("{$field}.{$prop}")->headline()->replace('.', ' ')->lower();

        yield "{$name} boolean error" => [fn () => [
            [$field => ['enabled' => 42]],
            null,
            false,
            [
                "{$field}.{$prop}" => [
                    __('validation.boolean', ['attribute' => $name]),
                ],
            ],
        ]];

        $prop = 'minWordSizeForTypos';
        $name = Str::of("{$field}.{$prop}")->headline()->replace('.', ' ')->lower();

        yield "{$name} not array nor assoc" => [fn () => [
            [$field => [$prop => 42]],
            null,
            false,
            [
                "{$field}.{$prop}" => [
                    Str::replace(':attribute', $name, App::make(ArrayAssocRule::class)->message()),
                ],
            ],
        ]];

        yield "{$name} array not assoc" => [fn () => [
            [$field => [$prop => [42]]],
            null,
            false,
            [
                "{$field}.{$prop}" => [
                    Str::replace(':attribute', $name, App::make(ArrayAssocRule::class)->message()),
                ],
            ],
        ]];

        foreach (['oneTypo', 'twoTypos'] as $size) {
            $name = Str::of("{$field}.{$prop}.{$size}")->headline()->replace('.', ' ')->lower();

            yield "{$name} integer error" => [fn () => [
                [$field => [$prop => [$size => 'foo']]],
                null,
                false,
                [
                    "{$field}.{$prop}.{$size}" => [
                        __('validation.integer', ['attribute' => $name]),
                    ],
                ],
            ]];

            yield "{$name} between error low" => [fn () => [
                [$field => [$prop => [$size => -1]]],
                null,
                false,
                [
                    "{$field}.{$prop}.{$size}" => [
                        __('validation.between.numeric', ['attribute' => $name, 'min' => 0, 'max' => 255]),
                    ],
                ],
            ]];

            yield "{$name} between error high" => [fn () => [
                [$field => [$prop => [$size => 300]]],
                null,
                false,
                [
                    "{$field}.{$prop}.{$size}" => [
                        __('validation.between.numeric', ['attribute' => $name, 'min' => 0, 'max' => 255]),
                    ],
                ],
            ]];
        }

        foreach (['disableOnWords', 'disableOnAttributes'] as $prop) {
            $name = Str::of("{$field}.{$prop}")->headline()->replace('.', ' ')->lower();

            yield "{$name} not array" => [fn () => [
                [$field => [$prop => 42]],
                null,
                false,
                [
                    "{$field}.{$prop}" => [
                        __('validation.array', ['attribute' => $name]),
                    ],
                ],
            ]];

            yield "{$name} required error" => [fn () => [
                [$field => [$prop => [null]]],
                null,
                false,
                [
                    "{$field}.{$prop}.0" => [
                        __('validation.required', ['attribute' => "{$field}.{$prop}.0"]),
                    ],
                ],
            ]];

            yield "{$name} string error" => [fn () => [
                [$field => [$prop => [42]]],
                null,
                false,
                [
                    "{$field}.{$prop}.0" => [
                        __('validation.string', ['attribute' => "{$field}.{$prop}.0"]),
                    ],
                ],
            ]];
        }
    }
}
