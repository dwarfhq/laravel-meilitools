<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Creates index.
 */
interface CreatesIndex
{
    /**
     * Create index.
     *
     * @param string $index   Index name.
     * @param array  $options Index options.
     *
     * @return array
     */
    public function __invoke(string $index, array $options = []): array;
}
