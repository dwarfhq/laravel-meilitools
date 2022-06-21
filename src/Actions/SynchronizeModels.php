<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModels;
use Throwable;

/**
 * Synchronize model index.
 */
class SynchronizeModels implements SynchronizesModels
{
    /**
     * Synchronizes index action.
     *
     * @var \Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel
     */
    private SynchronizesModel $synchronizeModel;

    /**
     * Constructor.
     *
     * @param \Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel $synchronizeModel Synchronize action.
     */
    public function __construct(SynchronizesModel $synchronizeModel)
    {
        $this->synchronizeModel = $synchronizeModel;
    }

    /**
     * {@inheritDoc}
     *
     * @param bool $pretend Whether to pretend running the action.
     *
     * @uses \Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel
     */
    public function __invoke(array $classes, ?callable $callback = null, bool $pretend = false): void
    {
        foreach ($classes as $class) {
            $result = null;

            try {
                $result = ($this->synchronizeModel)($class, $pretend);
            } catch (Throwable $e) {
                $result = $e;
            }

            if ($callback) {
                $callback($class, $result);
            }
        }
    }
}
