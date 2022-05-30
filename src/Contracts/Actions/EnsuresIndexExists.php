<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Ensures index exists.
 */
interface EnsuresIndexExists
{
    /**
     * Ensure that the given index exists.
     *
     * @param string $index Index name.
     *
     * @return void
     */
    public function __invoke(string $index, array $options = []): void;
}
