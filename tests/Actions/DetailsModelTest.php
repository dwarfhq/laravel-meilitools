<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Dwarf\MeiliTools\Tests\TestCase;

uses(Dwarf\MeiliTools\Tests\TestCase::class);

/**
 * @internal
 */

/**
 * Test DetailsModel::__invoke() method.
 */
test('invoke', function () {
    try {
        $details = app()->make(DetailsModel::class)(Movie::class);
        $this->assertSame(Helpers::defaultSettings(Helpers::engineVersion()), $details);
    } finally {
        $this->deleteIndex(app(Movie::class)->searchableAs());
    }
});
