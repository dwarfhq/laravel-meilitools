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
     * @param array<string, mixed> $settings
     */
    public function passes(array $settings): bool;

    /**
     * Validate and get attributes.
     *
     * @param array<string, mixed> $settings
     *
     * @return array<string, mixed>
     */
    public function validate(array $settings): array;

    /**
     * Get the validated data.
     *
     * @return array<string, mixed>|null
     */
    public function validated(): ?array;

    /**
     * Get the validation error messages.
     *
     * @return array<string, array<int, string>>
     */
    public function messages(): array;

    /**
     * Get the validation rules.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array;
}
