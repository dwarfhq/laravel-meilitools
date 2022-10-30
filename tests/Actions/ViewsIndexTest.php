<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ViewsIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use MeiliSearch\Exceptions\ApiException;
use MeiliSearch\Exceptions\CommunicationException;

/**
 * @internal
 */
class ViewsIndexTest extends TestCase
{
    /**
     * Test index.
     *
     * @var string
     */
    private const INDEX = 'testing-views-index';

    /**
     * Test using wrong Scout driver.
     *
     * @return void
     */
    public function testMeiliToolsException(): void
    {
        config(['scout.driver' => null]);

        $this->expectException(MeiliToolsException::class);

        $action = $this->app->make(ViewsIndex::class);
        $info = ($action)(self::INDEX);
    }

    /**
     * Test getting index information when MeiliSearch isn't running.
     *
     * @return void
     */
    public function testCommunicationException(): void
    {
        config(['scout.meilisearch.host' => 'http://localhost:7777']);

        $this->expectException(CommunicationException::class);
        $this->expectExceptionMessage('Failed to connect to localhost port 7777');

        $action = $this->app->make(ViewsIndex::class);
        $info = ($action)(self::INDEX);
    }

    /**
     * Test getting index information when it doesn't exist.
     *
     * @return void
     */
    public function testApiException(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Index `' . self::INDEX . '` not found.');

        $action = $this->app->make(ViewsIndex::class);
        $info = ($action)(self::INDEX);
    }

    /**
     * Test ViewsIndex::__invoke() method.
     *
     * @return void
     */
    public function testInvoke(): void
    {
        $this->withIndex(self::INDEX, function () {
            $action = $this->app->make(ViewsIndex::class);
            $info = ($action)(self::INDEX);

            AssertableJson::fromArray($info)
                ->where('uid', self::INDEX)
                ->where('primaryKey', null)
                ->whereType('createdAt', 'string')
                ->whereType('updatedAt', 'string')
                ->interacted()
            ;
        });
    }

    /**
     * Test ViewsIndex::__invoke() method with stats.
     *
     * @return void
     */
    public function testInvokeWithStats(): void
    {
        $this->withIndex(self::INDEX, function () {
            $action = $this->app->make(ViewsIndex::class);
            $info = ($action)(self::INDEX, true);

            AssertableJson::fromArray($info)
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
