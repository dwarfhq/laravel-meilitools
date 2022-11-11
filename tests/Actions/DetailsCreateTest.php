<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Actions;

use Dwarf\MeiliTools\Contracts\Actions\CreatesIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\TestCase;
use MeiliSearch\Exceptions\ApiException;
use MeiliSearch\Exceptions\CommunicationException;

/**
 * @internal
 */
class DetailsCreateTest extends TestCase
{
    /**
     * Test index.
     *
     * @var string
     */
    private const INDEX = 'testing-create-index';

    /**
     * Test using wrong Scout driver.
     *
     * @return void
     */
    public function testMeiliToolsException(): void
    {
        config(['scout.driver' => null]);

        $this->expectException(MeiliToolsException::class);

        $action = $this->app->make(CreatesIndex::class);
        $create = ($action)(self::INDEX);
    }

    /**
     * Test creating index when MeiliSearch isn't running.
     *
     * @return void
     */
    public function testCommunicationException(): void
    {
        config(['scout.meilisearch.host' => 'http://localhost:7777']);

        $this->expectException(CommunicationException::class);
        $this->expectExceptionMessage('Failed to connect to localhost port 7777');

        $action = $this->app->make(CreatesIndex::class);
        $create = ($action)(self::INDEX);
    }

    /**
     * Test CreatesIndex::__invoke() method.
     *
     * @return void
     */
    public function testInvoke(): void
    {
        try {
            $action = $this->app->make(CreatesIndex::class);
            $create = ($action)(self::INDEX);
            $this->assertArrayHasKey('enqueuedAt', $create);
            $this->assertEquals(self::INDEX, $create['indexUid']);
            $this->assertEquals('enqueued', $create['status']);
            $this->assertEquals('indexCreation', $create['type']);
        } finally {
            $this->delete(self::INDEX);
        }
    }
}
