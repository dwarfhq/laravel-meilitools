<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Tests\Models\Movie;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Test `meili:model:reindex` command.
 */
test('reindex', function (): void {
    Schema::create('movies', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
    Movie::withoutSyncingToSearch(function (): void {
        Movie::create(['name' => 'Batman']);
        Movie::create(['name' => 'Superman']);
    });

    try {
        $this->artisan('meili:model:reindex', ['model' => 'Movie', '--chunk' => 1])
            ->expectsOutput('Imported 1 model')
            ->expectsOutput('Imported 2 models')
            ->expectsOutput('Reindexed ' . Movie::class . ' with 2 models')
            ->assertSuccessful()
        ;
    } finally {
        $this->deleteIndex('testing-movies');
    }
});
