<?php

declare(strict_types=1);
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;

/**
 * Test `meili:index:delete` command with default settings.
 */
test('with default settings', function (): void {
    $this->withIndex('testing-delete-index', function (): void {
        $this->artisan('meili:index:delete')
            ->expectsQuestion('What is the index name?', 'testing-delete-index')
            ->expectsConfirmation('Are you sure you want to run this command?', 'no')
            ->assertFailed()
        ;

        $this->artisan('meili:index:delete')
            ->expectsQuestion('What is the index name?', 'testing-delete-index')
            ->expectsConfirmation('Are you sure you want to run this command?', 'yes')
            ->assertSuccessful()
        ;
    });
});

/**
 * Test `meili:index:delete` command with specified name.
 */
test('with specified name', function (): void {
    $this->withIndex('testing-delete-index', function (): void {
        $this->artisan('meili:index:delete', ['index' => 'testing-delete-index'])
            ->expectsConfirmation('Are you sure you want to run this command?', 'no')
            ->assertFailed()
        ;

        $this->artisan('meili:index:delete', ['index' => 'testing-delete-index'])
            ->expectsConfirmation('Are you sure you want to run this command?', 'yes')
            ->assertSuccessful()
        ;
    });
});

/**
 * Test `meili:index:delete` command with force option.
 */
test('with force option', function (): void {
    $this->withIndex('testing-delete-index', function (): void {
        $this->artisan('meili:index:delete', ['index' => 'testing-delete-index', '--force' => true])
            ->assertSuccessful()
        ;
    });
});

/**
 * Test `meili:index:delete` command without an index name.
 */
test('without index name', function (): void {
    $this->artisan('meili:index:delete')
        ->expectsQuestion('What is the index name?', '')
    ;
})->throws(MeiliToolsException::class, 'An index name is required');
