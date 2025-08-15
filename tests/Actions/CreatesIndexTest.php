<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Actions;

use Dwarf\MeiliTools\Contracts\Actions\CreatesIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use MeiliSearch\Exceptions\CommunicationException;

/**
 * @internal
 */
class CreatesIndexTest extends TestCase
{
    /**
     * Test index.
     *
     * @var string
     */
    private const INDEX = 'testing-creates-index';

    /**
     * Test using wrong Scout driver.
     */
    public function test_meili_tools_exception(): void
    {
        config(['scout.driver' => null]);

        $this->expectException(MeiliToolsException::class);

        $action = $this->app->make(CreatesIndex::class);
        $info = ($action)(self::INDEX);
    }

    /**
     * Test creating index when MeiliSearch isn't running.
     */
    public function test_communication_exception(): void
    {
        config(['scout.meilisearch.host' => 'http://localhost:7777']);

        $this->expectException(CommunicationException::class);
        $this->expectExceptionMessage('Failed to connect to localhost port 7777');

        $action = $this->app->make(CreatesIndex::class);
        $info = ($action)(self::INDEX);
    }

    /**
     * Test CreatesIndex::__invoke() method.
     */
    public function test_invoke(): void
    {
        try {
            $action = $this->app->make(CreatesIndex::class);
            $info = ($action)(self::INDEX);

            AssertableJson::fromArray($info)
                ->where('uid', self::INDEX)
                ->where('primaryKey', null)
                ->whereType('createdAt', 'string')
                ->whereType('updatedAt', 'string')
                ->interacted()
            ;
        } finally {
            $this->deleteIndex(self::INDEX);
        }
    }

    /**
     * Test CreatesIndex::__invoke() method with options.
     */
    public function test_invoke_with_options(): void
    {
        try {
            $action = $this->app->make(CreatesIndex::class);
            $info = ($action)(self::INDEX, ['primaryKey' => 'id']);

            AssertableJson::fromArray($info)
                ->where('uid', self::INDEX)
                ->where('primaryKey', 'id')
                ->whereType('createdAt', 'string')
                ->whereType('updatedAt', 'string')
                ->interacted()
            ;
        } finally {
            $this->deleteIndex(self::INDEX);
        }
    }
}
