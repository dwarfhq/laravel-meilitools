<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Feature\Actions;

use Closure;
use Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings;
use Dwarf\MeiliTools\Contracts\Rules\ArrayAssocRule;
use Dwarf\MeiliTools\Tests\TestCase;
use MeiliSearch\MeiliSearch;

/**
 * @internal
 */
class ValidatesIndexSettingsTest extends TestCase
{
    /**
     * Action instance.
     *
     * @var \Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings
     */
    protected ValidatesIndexSettings $action;

    /**
     * Test ValidatesIndexSettings::rules() method.
     *
     * @return void
     */
    public function testRules(): void
    {
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
            'synonyms'               => ['nullable', $this->app->make(ArrayAssocRule::class), 'min:1'],
            'synonyms.*'             => ['required', 'array', 'min:1'],
            'synonyms.*.*'           => ['required', 'string'],
        ];
        // Add typo tolerance to validation rules for version >=0.23.2.
        if (version_compare(MeiliSearch::VERSION, '0.23.2', '>=')) {
            $expected['typoTolerance'] = ['nullable', $this->app->make(ArrayAssocRule::class)];
        }
        $actual = $this->action->rules();

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    /**
     * Test ValidatesIndexSettings::passes() method.
     *
     * @dataProvider passesProvider
     *
     * @param \Closure $data Closure with test data.
     *
     * @return void
     */
    public function testPasses(Closure $data): void
    {
        [$value, $validated, $passes, $messages] = $data();

        $actualPasses = $this->action->passes($value);
        $this->assertSame($passes, $actualPasses);

        $actualValidated = $this->action->validated();
        $this->assertSame($validated, $actualValidated);

        $actualMessages = $this->action->messages();
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
        yield 'empty array' => [fn () => [[], [], true, []]];

        yield 'ranking rules null' => [fn () => [['rankingRules' => null], ['rankingRules' => null], true, []]];

        yield 'ranking rules list' => [fn () => [
            ['rankingRules' => ['words', 'typo', 'proximity']],
            ['rankingRules' => ['words', 'typo', 'proximity']],
            true,
            [],
        ]];

        yield 'ranking rules empty error' => [fn () => [
            ['rankingRules' => []],
            null,
            false,
            [
                'rankingRules' => [
                    __('validation.min.array', ['attribute' => 'ranking rules', 'min' => 1]),
                ],
            ],
        ]];

        // yield 'empty class and style' => [fn () => [['class' => [], 'style' => []], true, []]];
        //
        // yield 'complete class and style' => [fn () => [
        //                 [
        //         'class' => [
        //             'test1',
        //             'test2',
        //             'test3',
        //         ],
        //         'style' => [
        //             'width'  => '100%',
        //             'height' => '100%',
        //         ],
        //     ],
        //     true,
        //     [],
        // ]];
        //
        // yield 'string' => [fn () => [
        //                 'test',
        //     false,
        //     [
        //         $attribute => [
        //             __('validation.array', compact('attribute')),
        //         ],
        //     ],
        // ]];
        //
        // yield 'null class and string style' => [fn () => [
        //                 ['class' => null, 'style' => 'test'],
        //     false,
        //     [
        //         $attribute . '.class' => [
        //             __('validation.array', ['attribute' => $attribute . '.class']),
        //         ],
        //         $attribute . '.style' => [
        //             __('validation.array', ['attribute' => $attribute . '.style']),
        //         ],
        //     ],
        // ]];
        //
        // yield 'class with null entry and style with null value' => [fn () => [
        //                 ['class' => [null], 'style' => ['test' => null]],
        //     false,
        //     [
        //         $attribute . '.class.0' => [
        //             __('validation.required', ['attribute' => $attribute . '.class.0']),
        //         ],
        //         $attribute . '.style.test' => [
        //             __('validation.required', ['attribute' => $attribute . '.style.test']),
        //         ],
        //     ],
        // ]];
    }

    public function testValidationPasses(): void
    {
        $settings = [
            'rankingRules' => [
                'words',
                'typo',
                'proximity',
                'attribute',
                'sort',
                'exactness',
                'release_date:desc',
                'rank:desc',
            ],
            'distinctAttribute'    => 'movie_id',
            'searchableAttributes' => [
                'title',
                'overview',
                'genres',
            ],
            'displayedAttributes' => [
                'title',
                'overview',
                'genres',
                'release_date',
            ],
            'stopWords' => [
                'the',
                'a',
                'an',
            ],
            'sortableAttributes' => [
                'title',
                'release_date',
            ],
            'synonyms' => [
                'wolverine' => ['xmen', 'logan'],
                'logan'     => ['wolverine'],
            ],
        ];

        $this->assertTrue($this->action->passes($settings));
    }

    /**
     * Setup.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->action = $this->app->make(ValidatesIndexSettings::class);
    }
}
