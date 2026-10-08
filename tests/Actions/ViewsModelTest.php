<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\ViewsModel;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Illuminate\Testing\Fluent\AssertableJson;

/**
 * Test ViewsModel::__invoke() method.
 */
test('invoke', function (): void {
    $model = resolve(Movie::class);
    $index = $model->searchableAs();
    $primaryKey = $model->getKeyName();

    try {
        $info = resolve(ViewsModel::class)(Movie::class);

        AssertableJson::fromArray($info)
            ->where('uid', $index)
            ->where('primaryKey', $primaryKey)
            ->whereType('createdAt', 'string')
            ->whereType('updatedAt', 'string')
            ->interacted()
        ;
    } finally {
        $this->deleteIndex($index);
    }
});

/**
 * Test ViewsModel::__invoke() method with stats.
 */
test('invoke with stats', function (): void {
    $model = resolve(Movie::class);
    $index = $model->searchableAs();
    $primaryKey = $model->getKeyName();

    try {
        $info = resolve(ViewsModel::class)(Movie::class, true);

        AssertableJson::fromArray($info)
            ->where('uid', $index)
            ->where('primaryKey', $primaryKey)
            ->whereType('createdAt', 'string')
            ->whereType('updatedAt', 'string')
            ->where('numberOfDocuments', 0)
            ->where('isIndexing', false)
            ->etc()
            ->interacted()
        ;
    } finally {
        $this->deleteIndex($index);
    }
});
