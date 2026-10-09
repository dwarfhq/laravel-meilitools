<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Views stats.
 */
interface ViewsStats
{
    /**
     * Get the stats of the instance, or of the given index.
     *
     * @return array<string, mixed>
     */
    public function __invoke(?string $index = null): array;
}
