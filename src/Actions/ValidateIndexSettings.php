<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings;
use Dwarf\MeiliTools\Contracts\Rules\ArrayAssocRule;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

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
     */
    public function passes(array $settings): bool
    {
        $validator = Validator::make($settings, $this->rules());
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
     * @throws \Illuminate\Validation\ValidationException On validation failure.
     */
    public function validate(array $settings): array
    {
        return Validator::make($settings, $this->rules())->validate();
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
     */
    public function rules(): array
    {
        return [
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
            'synonyms'               => ['nullable', 'array', 'min:1', App::make(ArrayAssocRule::class)],
            'synonyms.*'             => ['required', 'array', 'min:1'],
            'synonyms.*.*'           => ['required', 'string'],
        ];
    }
}
