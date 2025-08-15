<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;

/**
 * @internal
 */

/**
 * Test using wrong Scout driver.
 */
test('meili tools exception', function () {
    config(['scout.driver' => null]);

    $this->expectException(MeiliToolsException::class);

    $action = app()->make(EnsuresIndexExists::class);
    $details = ($action)(self::INDEX);
});

/**
 * Test EnsuresIndexExists::__invoke() method.
 *
 * @doesNotPerformAssertions
 */
test('invoke', function () {
    try {
        app()->make(EnsuresIndexExists::class)(self::INDEX);
    } finally {
        $this->deleteIndex(self::INDEX);
    }
});
