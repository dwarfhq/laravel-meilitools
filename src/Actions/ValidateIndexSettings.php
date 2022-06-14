<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings;
use Dwarf\MeiliTools\Contracts\Rules\ArrayAssocRule;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use MeiliSearch\MeiliSearch;

/**
 * Validates index settings.
 */
class ValidateIndexSettings implements ValidatesIndexSettings
{
    /**
     * Validated data.
     *
     * @var array|null
     */
    protected ?array $validated = null;

    /**
     * Validation error messages.
     *
     * @var array
     */
    protected array $messages = [];

    /**
     * {@inheritDoc}
     *
     * @param string|null $version MeiliSearch engine version.
     */
    public function passes(array $settings, ?string $version = null): bool
    {
        $validator = Validator::make($settings, $this->rules($version), [], $this->attributes());
        if ($validator->fails()) {
            $this->validated = null;
            $this->messages = $validator->messages()->toArray();

            return false;
        }

        $this->validated = $validator->validated();
        $this->messages = [];

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @param string|null $version MeiliSearch engine version.
     *
     * @throws \Illuminate\Validation\ValidationException On validation failure.
     */
    public function validate(array $settings, ?string $version = null): array
    {
        return Validator::make($settings, $this->rules($version), [], $this->attributes())->validate();
    }

    /**
     * {@inheritDoc}
     */
    public function validated(): ?array
    {
        return $this->validated;
    }

    /**
     * {@inheritDoc}
     */
    public function messages(): array
    {
        return $this->messages;
    }

    /**
     * {@inheritDoc}
     *
     * @param string|null $version MeiliSearch engine version.
     */
    public function rules(?string $version = null): array
    {
        $rules = [
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
            'synonyms'               => ['sometimes', 'nullable', App::make(ArrayAssocRule::class)],
            'synonyms.*'             => ['required', 'array'],
            'synonyms.*.*'           => ['required', 'string'],
        ];

        // Add typo tolerance to validation rules for version >=0.23.2.
        if (version_compare(MeiliSearch::VERSION, '0.23.2', '>=')) {
            $rules['typoTolerance'] = ['sometimes', 'nullable', App::make(ArrayAssocRule::class)];
        }

        // Add actual typo tolerance validation rules for engine version >=0.27.0.
        if ($version && version_compare($version, '0.27.0', '>=')) {
            $rules['typoTolerance.enabled'] = ['sometimes', 'nullable', 'boolean'];
            $rules['typoTolerance.minWordSizeForTypos'] = ['sometimes', 'nullable', App::make(ArrayAssocRule::class)];
            $rules['typoTolerance.minWordSizeForTypos.oneTypo'] = [
                'sometimes',
                'nullable',
                'integer',
                'between:0,255',
            ];
            $rules['typoTolerance.minWordSizeForTypos.twoTypos'] = [
                'sometimes',
                'nullable',
                'integer',
                'between:0,255',
            ];
            $rules['typoTolerance.disableOnWords'] = ['sometimes', 'nullable', 'array'];
            $rules['typoTolerance.disableOnWords.*'] = ['required', 'string'];
            $rules['typoTolerance.disableOnAttributes'] = ['sometimes', 'nullable', 'array'];
            $rules['typoTolerance.disableOnAttributes.*'] = ['required', 'string'];
        }

        return $rules;
    }

    /**
     * Custom attribute values for typo tolerance rules.
     *
     * @return array
     */
    public function attributes(): array
    {
        $fields = [
            'typoTolerance.enabled',
            'typoTolerance.minWordSizeForTypos',
            'typoTolerance.minWordSizeForTypos.oneTypo',
            'typoTolerance.minWordSizeForTypos.twoTypos',
            'typoTolerance.disableOnWords',
            'typoTolerance.disableOnAttributes',
        ];

        return collect($fields)
            ->mapWithKeys(fn ($field) => [$field => Str::of($field)->headline()->replace('.', ' ')->lower()])
            ->all()
        ;
    }
}
