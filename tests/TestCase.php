<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests;

use Closure;
use Dwarf\MeiliTools\MeiliToolsServiceProvider;
use Laravel\Prompts\Prompt;
use Laravel\Scout\ScoutServiceProvider;
use Meilisearch\Client;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * @internal
 */
class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Prompt fallbacks are static and sticky, so commands from previous tests would otherwise answer prompts.
        Closure::bind(static function (): void {
            Prompt::$shouldFallback = false;
            Prompt::$fallbacks = [];
        }, null, Prompt::class)();
    }

    protected function getPackageProviders($app): array
    {
        return [
            MeiliToolsServiceProvider::class,
            ScoutServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $path = __DIR__ . '/Models';
        $namespace = 'Dwarf\\MeiliTools\\Tests\\Models';
        $app['config']->set('meilitools.paths', [$path => $namespace]);
        $app['config']->set('scout.driver', 'meilisearch');
    }

    /**
     * Perform tests using the specified index.
     */
    protected function withIndex(string $index, Closure $callback): void
    {
        try {
            $this->createIndex($index);
            $callback();
        } finally {
            $this->deleteIndex($index);
        }
    }

    /**
     * Create index and wait for task completion.
     *
     * @param array<string, mixed> $options
     */
    protected function createIndex(string $index, array $options = []): void
    {
        $client = resolve(Client::class);
        $task = $client->createIndex($index, $options);
        $client->waitForTask($task['taskUid']);
    }

    /**
     * Delete index and wait for task completion.
     */
    protected function deleteIndex(string $index): void
    {
        $client = resolve(Client::class);
        $task = $client->deleteIndex($index);
        $client->waitForTask($task['taskUid']);
    }
}
