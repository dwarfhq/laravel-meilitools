<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions\Concerns;

use DateTimeInterface;
use Meilisearch\Endpoints\Indexes;

trait ExtractsIndexInformation
{
    /**
     * Get index data and stats.
     *
     * @return array<string, mixed>
     */
    protected function getIndexData(Indexes $index, bool $stats = false): array
    {
        return [
            'uid'        => $index->getUid(),
            'primaryKey' => $index->getPrimaryKey(),
            'createdAt'  => $index->getCreatedAt()?->format(DateTimeInterface::RFC3339_EXTENDED),
            'updatedAt'  => $index->getUpdatedAt()?->format(DateTimeInterface::RFC3339_EXTENDED),
        ] + ($stats ? $index->stats() : []);
    }
}
