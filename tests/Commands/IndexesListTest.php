<?php

declare(strict_types=1);

/**
 * Test `meili:indexes:list` command with default settings.
 */
test('with default settings', function (): void {
    $this->withIndex('testing-indexes-list', function (): void {
        // Since data returned from MeiliSearch includes microsecond precision timestamps,
        // it's impossible to validate the exact console output.
        $this->artisan('meili:indexes:list')
            ->expectsOutputToContain('testing-indexes-list')
            ->assertSuccessful()
        ;
    });
});

/**
 * Test `meili:indexes:list` command with stats option.
 */
test('with stats', function (): void {
    $this->withIndex('testing-indexes-list', function (): void {
        // Since data returned from MeiliSearch includes microsecond precision timestamps,
        // it's impossible to validate the exact console output.
        $this->artisan('meili:indexes:list', ['--stats' => true])
            ->expectsOutputToContain('testing-indexes-list')
            ->assertSuccessful()
        ;
    });
});
