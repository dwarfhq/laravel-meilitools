<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings;
use Dwarf\MeiliTools\Contracts\Rules\ArrayAssocRule;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Validates index settings.
 */
class ValidateIndexSettings implements ValidatesIndexSettings
{
    /**
     * Validated data.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $validated = null;

    /**
     * Validation error messages.
     *
     * @var array<string, array<int, string>>
     */
    protected array $messages = [];

    public function passes(array $settings): bool
    {
        $validator = $this->validator($settings);
        if ($validator->fails()) {
            $this->validated = null;
            $this->messages = $validator->errors()->toArray();

            return false;
        }

        $this->validated = $validator->validated();
        $this->messages = [];

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @throws ValidationException On validation failure.
     */
    public function validate(array $settings): array
    {
        return $this->validator($settings)->validate();
    }

    public function validated(): ?array
    {
        return $this->validated;
    }

    public function messages(): array
    {
        return $this->messages;
    }

    public function rules(): array
    {
        $assoc = resolve(ArrayAssocRule::class);
        $list = ['sometimes', 'nullable', 'list'];
        $string = ['required', 'string'];

        return [
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
            'filterableAttributes.*'                    => $string,
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
            'rankingRules'                              => [...$list, 'min:1'],
            'rankingRules.*'                            => $string,
            'searchCutoffMs'                            => ['sometimes', 'nullable', 'integer:strict', 'min:0'],
            'searchableAttributes'                      => [...$list, 'min:1'],
            'searchableAttributes.*'                    => $string,
            'separatorTokens'                           => $list,
            'separatorTokens.*'                         => $string,
            'sortableAttributes'                        => $list,
            'sortableAttributes.*'                      => $string,
            'stopWords'                                 => $list,
            'stopWords.*'                               => $string,
            'synonyms'                                  => ['sometimes', 'nullable', $assoc],
            'synonyms.*'                                => ['required', 'list'],
            'synonyms.*.*'                              => $string,
            'typoTolerance'                             => ['sometimes', 'nullable', $assoc],
            'typoTolerance.enabled'                     => ['sometimes', 'nullable', 'boolean:strict'],
            'typoTolerance.minWordSizeForTypos'         => ['sometimes', 'nullable', $assoc],
            'typoTolerance.minWordSizeForTypos.oneTypo' => [
                'sometimes',
                'nullable',
                'integer:strict',
                'between:0,255',
            ],
            'typoTolerance.minWordSizeForTypos.twoTypos' => [
                'sometimes',
                'nullable',
                'integer:strict',
                'between:0,255',
            ],
            'typoTolerance.disableOnWords'        => $list,
            'typoTolerance.disableOnWords.*'      => $string,
            'typoTolerance.disableOnAttributes'   => $list,
            'typoTolerance.disableOnAttributes.*' => $string,
            'typoTolerance.disableOnNumbers'      => ['sometimes', 'nullable', 'boolean:strict'],
        ];
    }

    /**
     * Custom attribute names for nested settings.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->filter(fn (string $field): bool => Str::contains($field, '.') && !Str::contains($field, '*'))
            ->mapWithKeys(fn (string $field): array => [
                $field => Str::of($field)->headline()->replace('.', ' ')->lower()->toString(),
            ])
            ->all()
        ;
    }

    /**
     * Create a validator for the given settings.
     *
     * @param array<string, mixed> $settings
     */
    protected function validator(array $settings): ValidatorContract
    {
        return Validator::make($settings, $this->rules(), [], $this->attributes());
    }
}
