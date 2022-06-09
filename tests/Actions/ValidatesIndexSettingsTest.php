<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Feature\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings;
use Dwarf\MeiliTools\Contracts\Rules\ArrayAssocRule;
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

        $actual = $action->rules();
        $expected = [
            'displayedAttributes'    => ['nullable', 'array', 'min:1'],
            'displayedAttributes.*'  => ['required', 'string'],
            'distinctAttribute'      => ['nullable', 'string'],
            'filterableAttributes'   => ['nullable', 'array', 'min:1'],
            'filterableAttributes.*' => ['required', 'string'],
            'rankingRules'           => ['nullable', 'array', 'min:1'],
            'rankingRules.*'         => ['required', 'string'],
            'searchableAttributes'   => ['nullable', 'array', 'min:1'],
            'searchableAttributes.*' => ['required', 'string'],
            'sortableAttributes'     => ['nullable', 'array', 'min:1'],
            'sortableAttributes.*'   => ['required', 'string'],
            'stopWords'              => ['nullable', 'array', 'min:1'],
            'stopWords.*'            => ['required', 'string'],
            'synonyms'               => ['nullable', 'array', $this->app->make(ArrayAssocRule::class), 'min:1'],
            'synonyms.*'             => ['required', 'array'],
            'synonyms.*.*'           => ['required', 'string'],
        ];
        // Add typo tolerance to validation rules for version >=0.23.2.
        if (version_compare(MeiliSearch::VERSION, '0.23.2', '>=')) {
            $expected['typoTolerance'] = ['nullable', 'array', $this->app->make(ArrayAssocRule::class)];
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

        $actualPasses = $action->passes($value);
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
                    __('validation.array', ['attribute' => 'synonyms']),
                    Str::replace(':attribute', 'synonyms', App::make(ArrayAssocRule::class)->message()),
                ],
            ],
        ]];

        yield 'synonyms empty error' => [fn () => [
            ['synonyms' => []],
            null,
            false,
            [
                'synonyms' => [
                    __('validation.min.array', ['attribute' => 'synonyms', 'min' => 1]),
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
                        __('validation.array', ['attribute' => 'typo tolerance']),
                        Str::replace(':attribute', 'typo tolerance', App::make(ArrayAssocRule::class)->message()),
                    ],
                ],
            ]];
        }
    }
}
