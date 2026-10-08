<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Ensures index exists.
 */
interface EnsuresIndexExists
{
    /**
     * Ensure index exists, creating it if missing.
     *
     * @param array<string, mixed> $options
     */
    public function __invoke(string $index, array $options = []): void;
}
