<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\BrokenMovie;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Dwarf\MeiliTools\Tests\Tools;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * Test SynchronizesModel::__invoke() method with a model without settings.
 */
test('without settings', function (): void {
    try {
        expect(resolve(SynchronizesModel::class)(Movie::class))->toBeEmpty();
    } finally {
        $this->deleteIndex(resolve(Movie::class)->searchableAs());
    }
});

/**
 * Test SynchronizesModel::__invoke() method with invalid settings.
 */
test('with invalid settings', function (): void {
    resolve(SynchronizesModel::class)(BrokenMovie::class);
    $this->deleteIndex(resolve(BrokenMovie::class)->searchableAs());
})->throws(ValidationException::class, 'The distinct attribute field must be a string.');

/**
 * Test SynchronizesModel::__invoke() method with advanced settings.
 */
test('with advanced settings', function (): void {
    try {
        $defaults = Helpers::defaultSettings();
        $settings = resolve(MeiliMovie::class)->meiliSettings();
        $expected = Tools::changes($defaults, $settings);

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);

        $changes = resolve(SynchronizesModel::class)(MeiliMovie::class);
        expect($changes)->toBe($expected);

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect(Arr::except($details, ['faceting', 'pagination', 'typoTolerance']))->toMatchArray($settings);
    } finally {
        $this->deleteIndex(resolve(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test SynchronizesModel::__invoke() method with pretend option.
 */
test('with pretend', function (): void {
    try {
        $defaults = Helpers::defaultSettings();
        $settings = resolve(MeiliMovie::class)->meiliSettings();
        $expected = Tools::changes($defaults, $settings);

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);

        $changes = resolve(SynchronizesModel::class)(MeiliMovie::class, true);
        expect($changes)->toBe($expected);

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);
    } finally {
        $this->deleteIndex(resolve(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test SynchronizesModel::__invoke() method with soft deletes enabled.
 */
test('with soft deletes enabled', function (): void {
    config(['scout.soft_delete' => true]);

    try {
        $defaults = Helpers::defaultSettings();
        $settings = resolve(MeiliMovie::class)->meiliSettings();
        array_unshift($settings['filterableAttributes'], '__soft_deleted');
        $expected = Tools::changes($defaults, $settings);

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);

        $changes = resolve(SynchronizesModel::class)(MeiliMovie::class);
        expect($changes)->toBe($expected);

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect(Arr::except($details, ['faceting', 'pagination', 'typoTolerance']))->toMatchArray($settings);
    } finally {
        $this->deleteIndex(resolve(MeiliMovie::class)->searchableAs());
    }
});
