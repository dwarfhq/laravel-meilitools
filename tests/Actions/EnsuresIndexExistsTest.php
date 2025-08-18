<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;

/**
 * Test using wrong Scout driver.
 */
test('meili tools exception', function () {
    config(['scout.driver' => null]);

    app()->make(EnsuresIndexExists::class)('testing-ensures-index');
})->throws(MeiliToolsException::class);

/**
 * Test EnsuresIndexExists::__invoke() method.
 */
test('invoke', function () {
    try {
        app()->make(EnsuresIndexExists::class)('testing-ensures-index');
    } finally {
        $this->deleteIndex('testing-ensures-index');
    }
})->throwsNoExceptions();
