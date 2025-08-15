<?php

declare(strict_types=1);



/**
 * @internal
 */

/**
 * Test `meili:index:delete` command with default settings.
 */
test('with default settings', function () {
    $this->withIndex(self::INDEX, function () {
        $this->artisan('meili:index:delete')
            ->expectsQuestion('What is the index name?', self::INDEX)
            ->expectsConfirmation('Are you sure you want to run this command?', 'no')
            ->assertFailed()
        ;

        $this->artisan('meili:index:delete')
            ->expectsQuestion('What is the index name?', self::INDEX)
            ->expectsConfirmation('Are you sure you want to run this command?', 'yes')
            ->assertSuccessful()
        ;
    });
});

/**
 * Test `meili:index:delete` command with specified name.
 */
test('with specified name', function () {
    $this->withIndex(self::INDEX, function () {
        $this->artisan('meili:index:delete', ['index' => self::INDEX])
            ->expectsConfirmation('Are you sure you want to run this command?', 'no')
            ->assertFailed()
        ;

        $this->artisan('meili:index:delete', ['index' => self::INDEX])
            ->expectsConfirmation('Are you sure you want to run this command?', 'yes')
            ->assertSuccessful()
        ;
    });
});

/**
 * Test `meili:index:delete` command with force option.
 */
test('with force option', function () {
    $this->withIndex(self::INDEX, function () {
        $this->artisan('meili:index:delete', ['index' => self::INDEX, '--force' => true])
            ->assertSuccessful()
        ;
    });
});
