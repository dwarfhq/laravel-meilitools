<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use MeiliSearch\Exceptions\CommunicationException;

/**
 * @internal
 */
class ListsIndexesTest extends TestCase
{
    /**
     * Test index.
     *
     * @var string
     */
    private const INDEX = 'testing-indexes-list';

    /**
     * Test using wrong Scout driver.
     */
    public function test_meili_tools_exception(): void
    {
        config(['scout.driver' => null]);

        $this->expectException(MeiliToolsException::class);

        $action = $this->app->make(ListsIndexes::class);
        $list = ($action)();
    }

    /**
     * Test getting index list when MeiliSearch isn't running.
     */
    public function test_communication_exception(): void
    {
        config(['scout.meilisearch.host' => 'http://localhost:7777']);

        $this->expectException(CommunicationException::class);
        $this->expectExceptionMessage('Failed to connect to localhost port 7777');

        $action = $this->app->make(ListsIndexes::class);
        $list = ($action)();
    }

    /**
     * Test ListsIndexes::__invoke() method.
     */
    public function test_invoke(): void
    {
        $this->withIndex(self::INDEX, function () {
            $action = $this->app->make(ListsIndexes::class);
            $list = ($action)();

            $this->assertArrayHasKey(self::INDEX, $list);
            AssertableJson::fromArray($list[self::INDEX])
                ->where('uid', self::INDEX)
                ->where('primaryKey', null)
                ->whereType('createdAt', 'string')
                ->whereType('updatedAt', 'string')
                ->interacted()
            ;
        });
    }

    /**
     * Test ListsIndexes::__invoke() method with stats.
     */
    public function test_invoke_with_stats(): void
    {
        $this->withIndex(self::INDEX, function () {
            $action = $this->app->make(ListsIndexes::class);
            $list = ($action)(true);

            $this->assertArrayHasKey(self::INDEX, $list);
            AssertableJson::fromArray($list[self::INDEX])
                ->where('uid', self::INDEX)
                ->where('primaryKey', null)
                ->whereType('createdAt', 'string')
                ->whereType('updatedAt', 'string')
                ->where('numberOfDocuments', 0)
                ->where('isIndexing', false)
                ->etc()
                ->interacted()
            ;
        });
    }
}
