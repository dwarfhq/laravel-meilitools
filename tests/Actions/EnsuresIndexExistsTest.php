<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Actions;

use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Tests\TestCase;

/**
 * @internal
 */
class EnsuresIndexExistsTest extends TestCase
{
    /**
     * Test index.
     *
     * @var string
     */
    private const INDEX = 'testing-ensures-index';

    /**
     * Test using wrong Scout driver.
     *
     * @return void
     */
    public function testMeiliToolsException(): void
    {
        config(['scout.driver' => null]);

        $this->expectException(MeiliToolsException::class);

        $action = $this->app->make(EnsuresIndexExists::class);
        $details = ($action)(self::INDEX);
    }

    /**
     * Test EnsuresIndexExists::__invoke() method.
     *
     * @doesNotPerformAssertions
     *
     * @return void
     */
    public function testInvoke(): void
    {
        try {
            $this->app->make(EnsuresIndexExists::class)(self::INDEX);
        } finally {
            $this->deleteIndex(self::INDEX);
        }
    }
}
