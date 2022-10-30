<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests;

use Closure;
use Dwarf\MeiliTools\MeiliToolsServiceProvider;
use Laravel\Scout\EngineManager;
use Laravel\Scout\ScoutServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * @internal
 */
class TestCase extends BaseTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getPackageProviders($app): array
    {
        return [
            MeiliToolsServiceProvider::class,
            ScoutServiceProvider::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function defineEnvironment($app): void
    {
        $path = __DIR__ . '/Models';
        $namespace = 'Dwarf\\MeiliTools\\Tests\\Models';
        $app['config']->set('meilitools.paths', [$path => $namespace]);
        $app['config']->set('scout.driver', 'meilisearch');
    }

    /**
     * Perform tests using the specified index.
     *
     * @param string   $index    Index name.
     * @param \Closure $callback Test callback function.
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
     * @param string $index   Index name.
     * @param array  $options Index options.
     *
     * @return void
     */
    protected function createIndex(string $index, array $options = []): void
    {
        $engine = $this->app->make(EngineManager::class)->engine();
        $task = $engine->createIndex($index, $options);
        $engine->waitForTask($task['taskUid']);
    }

    /**
     * Delete index and wait for task completion.
     *
     * @param string $index Index name.
     *
     * @return void
     */
    protected function deleteIndex(string $index): void
    {
        $engine = $this->app->make(EngineManager::class)->engine();
        $task = $engine->deleteIndex($index);
        $engine->waitForTask($task['taskUid']);
    }
}
