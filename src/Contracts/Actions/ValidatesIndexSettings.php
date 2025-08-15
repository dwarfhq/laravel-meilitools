<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Validates index settings.
 */
interface ValidatesIndexSettings
{
    /**
     * Determine if the validation passes.
     *
     * @param array $settings Settings.
     */
    public function passes(array $settings): bool;

    /**
     * Validate and get attributes.
     *
     * @param array $settings Settings.
     */
    public function validate(array $settings): array;

    /**
     * Get the validated data.
     */
    public function validated(): ?array;

    /**
     * Get the validation error messages.
     */
    public function messages(): array;

    /**
     * Get the validation rules.
     */
    public function rules(): array;
}
