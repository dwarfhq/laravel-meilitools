<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Support;

use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Dwarf\MeiliTools\Tests\TestCase;

/**
 * @internal
 */
class HelpersTest extends TestCase
{
    /**
     * Test Helpers::guessModelNamespace() method.
     *
     * @return void
     */
    public function testGuessModelNamespace(): void
    {
        $this->assertSame(Movie::class, Helpers::guessModelNamespace(Movie::class));
        $this->assertSame(Movie::class, Helpers::guessModelNamespace('Movie'));
        $this->assertSame('Fake', Helpers::guessModelNamespace('Fake'));
    }
}
