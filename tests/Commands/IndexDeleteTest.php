<?php

declare(strict_types=1);

/**
 * Test `meili:index:delete` command with default settings.
 */
test('with default settings', function () {
    $this->withIndex('testing-delete-index', function () {
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
test('with specified name', function () {
    $this->withIndex('testing-delete-index', function () {
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
test('with force option', function () {
    $this->withIndex('testing-delete-index', function () {
        $this->artisan('meili:index:delete', ['index' => 'testing-delete-index', '--force' => true])
            ->assertSuccessful()
        ;
    });
});
