<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Feature\Actions;

use Dwarf\MeiliTools\Contracts\Actions\ListsClasses;
use Dwarf\MeiliTools\Contracts\Indexes\MeiliSettings;
use Dwarf\MeiliTools\Tests\TestCase;

/**
 * @internal
 */
class ListsClassesTest extends TestCase
{
    /**
     * Test listing models using absolute and relative paths.
     *
     * @return void
     */
    public function testPathListing(): void
    {
        $action = $this->app->make(ListsClasses::class);

        $path = 'app/Models';
        $namespace = 'App\\Models';
        $classes = $action($path, $namespace);
        $this->assertCount(0, $classes);

        $path = base_path($path);
        $classes = $action($path, $namespace);
        $this->assertCount(0, $classes);

        $path = __DIR__ . '/../../Models';
        $namespace = 'Dwarf\\MeiliTools\\Tests\\Models';
        $classes = $action($path, $namespace);
        $this->assertCount(2, $classes);

        $classes = $action($path, $namespace, fn ($class) => is_a($class, MeiliSettings::class, true));
        $this->assertCount(1, $classes);
    }
}
