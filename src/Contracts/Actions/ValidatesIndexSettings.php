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
     *
     * @return bool
     */
    public function passes(array $settings): bool;

    /**
     * Validate and get attributes.
     *
     * @param array $settings Settings.
     *
     * @return array
     */
    public function validate(array $settings): array;

    /**
     * Get the validated data.
     *
     * @return array|null
     */
    public function validated(): ?array;

    /**
     * Get the validation error messages.
     *
     * @return array
     */
    public function messages(): array;

    /**
     * Get the validation rules.
     *
     * @return array
     */
    public function rules(): array;
}
