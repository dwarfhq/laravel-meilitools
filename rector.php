<?php

declare(strict_types=1);

use Pest\Rector\Rules\ChainExpectCallsRector;
use Pest\Rector\Set\PestSetList;
use Rector\CodingStyle\Rector\FuncCall\FunctionFirstClassCallableRector;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use RectorLaravel\Set\LaravelLevelSetList;

/*
 * Upgrade sets for the package, plus Pest's coding style set for the tests.
 * The general code quality, collection and dead code sets stay out of scope.
 */
$config = RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets()
    // Upgrade sets are capped at the lowest supported Laravel version, as composer based sets
    // follow the installed (newest) version and would break compatibility with older versions.
    ->withSets([LaravelLevelSetList::UP_TO_LARAVEL_120])
    ->withSkip([
        // An arrow function is not clearer for a multi-line body.
        ClosureToArrowFunctionRector::class,

        // `trim(...)` handed to `Collection::map` takes the key as its charlist.
        FunctionFirstClassCallableRector::class,
    ])
;

// The Pest plugin requires Pest 5, so it isn't installed when testing against Laravel 12 (Pest 4).
if (class_exists(PestSetList::class)) {
    $config
        ->withSets([PestSetList::CODING_STYLE])
        // Separate expect() calls on different variables in one test are merged
        // into a single chain joined by ->and().
        ->withConfiguredRule(ChainExpectCallsRector::class, [
            ChainExpectCallsRector::MERGE_DIFFERENT_VARIABLES => true,
        ])
    ;
}

return $config;
