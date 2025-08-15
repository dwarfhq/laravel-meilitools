<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ViewsModel;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Dwarf\MeiliTools\Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;

/**
 * @internal
 */
class ViewsModelTest extends TestCase
{
    /**
     * Test ViewsModel::__invoke() method.
     */
    public function test_invoke(): void
    {
        $model = app(Movie::class);
        $index = $model->searchableAs();
        $primaryKey = $model->getKeyName();

        try {
            $info = $this->app->make(ViewsModel::class)(Movie::class);

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
    }

    /**
     * Test ViewsModel::__invoke() method with stats.
     */
    public function test_invoke_with_stats(): void
    {
        $model = app(Movie::class);
        $index = $model->searchableAs();
        $primaryKey = $model->getKeyName();

        try {
            $info = $this->app->make(ViewsModel::class)(Movie::class, true);

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
    }
}
