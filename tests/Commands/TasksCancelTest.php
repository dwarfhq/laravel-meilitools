<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Exceptions\MeiliToolsException;

/**
 * Test `meili:tasks:cancel` command.
 */
test('cancel', function (): void {
    $this->withIndex('testing-tasks-cancel', function (): void {
        $this->artisan('meili:tasks:cancel', ['--index' => ['testing-tasks-cancel']])
            ->expectsConfirmation('Are you sure you want to run this command?', 'no')
            ->assertFailed()
        ;

        $this->artisan('meili:tasks:cancel', ['--index' => ['testing-tasks-cancel'], '--status' => ['enqueued']])
            ->expectsConfirmation('Are you sure you want to run this command?', 'yes')
            ->expectsOutput('Canceled 0 tasks')
            ->assertSuccessful()
        ;
    });
});

/**
 * Test `meili:tasks:cancel` command without filters.
 */
test('without filters', function (): void {
    $this->artisan('meili:tasks:cancel', ['--force' => true]);
})->throws(MeiliToolsException::class, 'At least one filter is required to cancel tasks');
