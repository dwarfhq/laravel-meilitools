<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModels;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\BrokenMovie;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Dwarf\MeiliTools\Tests\Tools;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * Test SynchronizesModels::__invoke() method with advanced settings.
 */
test('with advanced settings', function (): void {
    try {
        $defaults = Helpers::defaultSettings();
        $settings = resolve(MeiliMovie::class)->meiliSettings();
        $expected = Tools::changes($defaults, $settings);
        $exception = ValidationException::withMessages([
            'distinctAttribute' => ['The distinct attribute field must be a string.'],
        ]);

        $classes = [
            BrokenMovie::class => $exception,
            Movie::class       => [],
            MeiliMovie::class  => $expected,
        ];

        $details = resolve(DetailsModel::class)(Movie::class);
        expect($details)->toMatchArray($defaults);
        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);

        $action = resolve(SynchronizesModels::class);
        $action(array_keys($classes), function ($class, $result) use ($classes): void {
            if (is_array($result)) {
                expect($result)->toBe($classes[$class]);
            } else {
                $expected = $classes[$class];
                expect($expected)->toBeInstanceOf($result::class)
                    ->and($result->getMessage())->toBe($expected instanceof Throwable ? $expected->getMessage() : null)
                ;
            }
        });

        $details = resolve(DetailsModel::class)(Movie::class);
        expect($details)->toMatchArray($defaults);

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect(Arr::except($details, ['faceting', 'pagination', 'typoTolerance']))->toMatchArray($settings);
    } finally {
        $this->deleteIndex(resolve(BrokenMovie::class)->searchableAs());
        $this->deleteIndex(resolve(Movie::class)->searchableAs());
        $this->deleteIndex(resolve(MeiliMovie::class)->searchableAs());
    }
});

/**
 * Test SynchronizesModels::__invoke() method with pretend option.
 */
test('with pretend', function (): void {
    try {
        $defaults = Helpers::defaultSettings();
        $settings = resolve(MeiliMovie::class)->meiliSettings();
        $expected = Tools::changes($defaults, $settings);

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);

        $action = resolve(SynchronizesModels::class);
        $action([MeiliMovie::class], function ($class, $result) use ($expected): void {
            expect($result)->toBe($expected);
        }, true);

        $details = resolve(DetailsModel::class)(MeiliMovie::class);
        expect($details)->toMatchArray($defaults);
    } finally {
        $this->deleteIndex(resolve(MeiliMovie::class)->searchableAs());
    }
});
