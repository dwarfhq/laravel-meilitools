<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Views index.
 */
interface ViewsIndex
{
    /**
     * Get index information.
     *
     * @return array<string, mixed>
     */
    public function __invoke(string $index, bool $stats = false): array;
}
