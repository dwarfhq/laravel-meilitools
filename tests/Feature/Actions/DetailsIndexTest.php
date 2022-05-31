<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Feature\Actions;

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\TestCase;
use MeiliSearch\Exceptions\ApiException;
use MeiliSearch\Exceptions\CommunicationException;

/**
 * @internal
 */
class DetailsIndexTest extends TestCase
{
    /**
     * Test index.
     *
     * @var string
     */
    private const INDEX = 'testing-details-index';

    /**
     * Test using wrong Scout driver.
     *
     * @return void
     */
    public function testMeiliToolsException(): void
    {
        config(['scout.driver' => null]);

        $this->expectException(MeiliToolsException::class);

        $action = $this->app->make(DetailsIndex::class);
        $details = ($action)(self::INDEX);
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

        $action = $this->app->make(DetailsIndex::class);
        $details = ($action)(self::INDEX);
    }

    /**
     * Test getting index details when it doesn't exist.
     *
     * @return void
     */
    public function testApiException(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Index `' . self::INDEX . '` not found.');

        $action = $this->app->make(DetailsIndex::class);
        $details = ($action)(self::INDEX);
    }

    /**
     * Test DetailsIndex::__invoke() method.
     *
     * @return void
     */
    public function testInvoke(): void
    {
        $this->withIndex(self::INDEX, function () {
            $action = $this->app->make(DetailsIndex::class);
            $details = ($action)(self::INDEX);
            $this->assertSame(Helpers::defaultSettings(), $details);
        });
    }
}
