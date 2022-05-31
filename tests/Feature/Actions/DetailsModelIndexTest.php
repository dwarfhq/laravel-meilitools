<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Feature\Actions;

use Dwarf\MeiliTools\Contracts\Actions\DetailsModelIndex;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Dwarf\MeiliTools\Tests\TestCase;

/**
 * @internal
 */
class DetailsModelIndexTest extends TestCase
{
    /**
     * Test DetailsModelIndex::__invoke() method.
     *
     * @return void
     */
    public function testInvoke(): void
    {
        try {
            $details = $this->app->make(DetailsModelIndex::class)(Movie::class);
            $this->assertSame(Helpers::defaultSettings(), $details);
        } finally {
            $this->deleteIndex((new Movie())->searchableAs());
        }
    }
}
