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
     */
    public function test_invoke(): void
    {
        try {
            $details = $this->app->make(DetailsModel::class)(Movie::class);
            $this->assertSame(Helpers::defaultSettings(Helpers::engineVersion()), $details);
        } finally {
            $this->deleteIndex(app(Movie::class)->searchableAs());
        }
    }
}
