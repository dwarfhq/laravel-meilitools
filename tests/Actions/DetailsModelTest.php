<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Illuminate\Foundation\Auth\User;

/**
 * Test DetailsModel::__invoke() method.
 */
test('invoke', function (): void {
    try {
        $details = resolve(DetailsModel::class)(Movie::class);
        expect($details)->toMatchArray(Helpers::defaultSettings());
    } finally {
        $this->deleteIndex(resolve(Movie::class)->searchableAs());
    }
});

/**
 * Test DetailsModel::__invoke() method with a class which isn't a searchable model.
 */
test('with unsearchable class', function (): void {
    resolve(DetailsModel::class)(User::class);
})->throws(MeiliToolsException::class, "Class 'Illuminate\\Foundation\\Auth\\User' is not a searchable model");
