<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Details index.
 */
interface DetailsIndex
{
    /**
     * Get index settings.
     *
     * @return array<string, mixed>
     */
    public function __invoke(string $index): array;
}
