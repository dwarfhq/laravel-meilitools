<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Creates index.
 */
interface CreatesIndex
{
    /**
     * Create a new index.
     *
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed> Index information.
     */
    public function __invoke(string $index, array $options = []): array;
}
