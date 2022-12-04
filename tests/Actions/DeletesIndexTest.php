<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Actions;

use Dwarf\MeiliTools\Contracts\Actions\DeletesIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Tests\TestCase;
use MeiliSearch\Exceptions\CommunicationException;

/**
 * @internal
 */
class DeletesIndexTest extends TestCase
{
    /**
     * Test index.
     *
     * @var string
     */
    private const INDEX = 'testing-deletes-index';

    /**
     * Test using wrong Scout driver.
     *
     * @return void
     */
    public function testMeiliToolsException(): void
    {
        config(['scout.driver' => null]);

        $this->expectException(MeiliToolsException::class);

        $action = $this->app->make(DeletesIndex::class);
        $delete = ($action)(self::INDEX);
    }

    /**
     * Test deleting index when MeiliSearch isn't running.
     *
     * @return void
     */
    public function testCommunicationException(): void
    {
        config(['scout.meilisearch.host' => 'http://localhost:7777']);

        $this->expectException(CommunicationException::class);
        $this->expectExceptionMessage('Failed to connect to localhost port 7777');

        $action = $this->app->make(DeletesIndex::class);
        ($action)(self::INDEX);
    }

    /**
     * Test deleting index when it doesn't exist.
     *
     * @return void
     */
    public function testIndexMissing(): void
    {
        // No errors will be thrown in this case.
        $action = $this->app->make(DeletesIndex::class);
        ($action)(self::INDEX);
    }

    /**
     * Test DeletesIndex::__invoke() method.
     *
     * @return void
     */
    public function testInvoke(): void
    {
        $this->createIndex(self::INDEX);
        $action = $this->app->make(DeletesIndex::class);
        ($action)(self::INDEX);
    }
}
