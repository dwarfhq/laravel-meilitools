<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Feature\Actions;

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
            $this->assertSame(Helpers::defaultSettings(), $details);
        } finally {
            $this->deleteIndex((new Movie())->searchableAs());
        }
    }
}
