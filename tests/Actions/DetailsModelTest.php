<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Actions;

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Dwarf\MeiliTools\Tests\TestCase;

/**
 * @internal
 */
class DetailsModelTest extends TestCase
{
    /**
     * Test DetailsModel::__invoke() method.
     *
     * @return void
     */
    public function testInvoke(): void
    {
        try {
            $details = $this->app->make(DetailsModel::class)(Movie::class);
            $this->assertSame(Helpers::defaultSettings(Helpers::engineVersion()), $details);
        } finally {
            $this->deleteIndex(app(Movie::class)->searchableAs());
        }
    }

    /**
     * Test DetailsModel::__invoke() method with short model name.
     *
     * @return void
     */
    public function testInvokeWithShortModelName(): void
    {
        $path = __DIR__ . '/../Models';
        $namespace = 'Dwarf\\MeiliTools\\Tests\\Models';
        config(['meilitools.paths' => [$path => $namespace]]);

        try {
            $details = $this->app->make(DetailsModel::class)('Movie');
            $this->assertSame(Helpers::defaultSettings(Helpers::engineVersion()), $details);
        } finally {
            $this->deleteIndex(app(Movie::class)->searchableAs());
        }
    }
}
