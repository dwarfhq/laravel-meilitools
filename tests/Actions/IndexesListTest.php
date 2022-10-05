<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Tests\TestCase;
use MeiliSearch\Exceptions\CommunicationException;

/**
 * @internal
 */
class IndexesListTest extends TestCase
{
    /**
     * Test index.
     *
     * @var string
     */
    private const INDEX = 'testing-index-list';

    /**
     * Test using wrong Scout driver.
     *
     * @return void
     */
    public function testMeiliToolsException(): void
    {
        config(['scout.driver' => null]);

        $this->expectException(MeiliToolsException::class);

        $action = $this->app->make(ListsIndexes::class);
        $details = ($action)();
    }

    /**
     * Test getting index details when MeiliSearch isn't running.
     *
     * @return void
     */
    public function testCommunicationException(): void
    {
        config(['scout.meilisearch.host' => 'http://localhost:7777']);

        $this->expectException(CommunicationException::class);
        $this->expectExceptionMessage('Failed to connect to localhost port 7777');

        $action = $this->app->make(ListsIndexes::class);
        $details = ($action)();
    }

    /**
     * Test ListsIndexes::__invoke() method.
     *
     * @return void
     */
    public function testInvoke(): void
    {
        $this->withIndex(self::INDEX, function () {
            $action = $this->app->make(ListsIndexes::class);
            $details = ($action)();
            $this->assertArrayHasKey(self::INDEX, $details);
            $this->assertEquals(self::INDEX, $details[self::INDEX]['uid']);
        });
    }
}
