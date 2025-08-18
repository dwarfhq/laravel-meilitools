<?php

declare(strict_types=1);

/**
 * Test `meili:index:view` command with default settings.
 */
test('with default settings', function () {
    $this->withIndex('testing-index-view', function () {
        // Since data returned from MeiliSearch includes microsecond precision timestamps,
        // it's impossible to validate the exact console output.
        $this->artisan('meili:index:view')
            ->expectsQuestion('What is the index name?', 'testing-index-view')
            ->expectsOutputToContain('testing-index-view')
            ->assertSuccessful()
        ;

        $this->artisan('meili:index:view', ['index' => 'testing-index-view'])
            ->expectsOutputToContain('testing-index-view')
            ->assertSuccessful()
        ;
    });
});

/**
 * Test `meili:index:view` command with stats option.
 */
test('with stats', function () {
    $this->withIndex('testing-index-view', function () {
        // Since data returned from MeiliSearch includes microsecond precision timestamps,
        // it's impossible to validate the exact console output.
        $this->artisan('meili:index:view', ['index' => 'testing-index-view', '--stats' => true])
            ->expectsOutputToContain('testing-index-view')
            ->assertSuccessful()
        ;
    });
});
