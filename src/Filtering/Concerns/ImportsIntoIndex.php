<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering\Concerns;

use Dwarf\MeiliTools\Filtering\RedirectingClient;
use Illuminate\Database\Eloquent\Collection;

/**
 * Imports models into another index in a Scout MeiliSearch engine, e.g. to reindex without downtime.
 */
trait ImportsIntoIndex
{
    public function importInto(string $index, Collection $models): void
    {
        $model = $models->first();
        if ($model === null || !method_exists($model, 'indexableAs')) {
            return;
        }

        // Scout writes to the model's own index, so the client redirects it to the given index while updating.
        $client = $this->meilisearch;
        $this->meilisearch = new RedirectingClient(
            $model->indexableAs(),
            $index,
            (string) config('scout.meilisearch.host'),
            \is_string(config('scout.meilisearch.key')) ? config('scout.meilisearch.key') : null,
        );

        try {
            $this->update($models);
        } finally {
            $this->meilisearch = $client;
        }
    }
}
