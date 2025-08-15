<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions\Concerns;

use Meilisearch\Endpoints\Indexes;

trait ExtractsIndexInformation
{
    /**
     * Get index data and stats.
     *
     * @param \MeiliSearch\Endpoints\Indexes $index Index.
     * @param bool                           $stats Whether to include stats.
     */
    protected function getIndexData(Indexes $index, bool $stats = false): array
    {
        return [
            'uid'        => $index->getUid(),
            'primaryKey' => $index->getPrimaryKey(),
            'createdAt'  => $index->getCreatedAt(),
            'updatedAt'  => $index->getUpdatedAt(),
        ] + ($stats ? $index->stats() : []);
    }
}
