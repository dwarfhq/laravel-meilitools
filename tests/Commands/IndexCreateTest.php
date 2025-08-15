<?php

declare(strict_types=1);

/**
 * Test `meili:index:create` command with default settings.
 */
test('with default settings', function () {
    try {
        $this->artisan('meili:index:create')
            ->expectsQuestion('What is the index name?', 'testing-create-index')
            ->assertSuccessful()
        ;
    } finally {
        $this->deleteIndex('testing-create-index');
    }

    try {
        $this->artisan('meili:index:create', ['index' => 'testing-create-index'])
            ->assertSuccessful()
        ;
    } finally {
        $this->deleteIndex('testing-create-index');
    }
});
