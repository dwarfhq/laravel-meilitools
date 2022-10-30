<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions\Concerns;

use MeiliSearch\Endpoints\Indexes;

trait ExtractsIndexInformation
{
    /**
     * Get index data and stats.
     *
     * @param \MeiliSearch\Endpoints\Indexes $index Index.
     * @param bool                           $stats Whether to include stats.
     *
     * @return array
     */
    protected function getIndexData(Indexes $index, bool $stats = false): array
    {
        return [
            'uid'        => $index->getUid(),
            'primaryKey' => $index->getPrimaryKey(),
            'createdAt'  => $index->getCreatedAtString(),
            'updatedAt'  => $index->getUpdatedAtString(),
        ] + ($stats ? $index->stats() : []);
    }
}
