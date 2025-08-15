<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Actions;

/**
 * Resets model index.
 */
interface ResetsModel
{
    /**
     * Resets model index details.
     *
     * @param string $class Model class.
     */
    public function __invoke(string $class): array;
}
