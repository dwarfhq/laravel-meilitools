<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Actions;

use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\TestCase;
use Dwarf\MeiliTools\Tests\Tools;
use Illuminate\Support\Arr;

/**
 * @internal
 */
class SynchronizesIndexTest extends TestCase
{
    /**
     * Test index.
     *
     * @var string
     */
    private const INDEX = 'testing-synchronizes-index';

    /**
     * Test SynchronizesIndex::__invoke() method with movie settings.
     */
    public function test_with_changing_movie_settings(): void
    {
        $this->withIndex(self::INDEX, function () {
            $action = $this->app->make(SynchronizesIndex::class);

            $changes = ($action)(self::INDEX, []);
            $this->assertSame([], $changes);

            $defaults = Helpers::defaultSettings(Helpers::engineVersion());
            $settings = Tools::movieSettings();

            $changes = ($action)(self::INDEX, $settings);
            $this->assertCount(8, $changes);

            foreach ($changes as $key => $value) {
                $old = $defaults[$key];
                $new = $settings[$key];
                $this->assertSame(compact('old', 'new'), $value);
            }

            $update1 = [
                'stopWords'          => null,
                'sortableAttributes' => null,
                'synonyms'           => null,
            ];
            $changes = ($action)(self::INDEX, $update1);
            $this->assertCount(3, $changes);

            foreach ($changes as $key => $value) {
                $old = $settings[$key];
                $new = $update1[$key];
                $this->assertSame(compact('old', 'new'), $value);
            }

            $update2 = [
                'distinctAttribute'   => 'movie_id',
                'displayedAttributes' => null,
                'stopWords'           => null,
            ];
            $changes = ($action)(self::INDEX, $update2);
            $this->assertCount(1, $changes);

            foreach ($changes as $key => $value) {
                $old = $settings[$key];
                $new = $update2[$key];
                $this->assertSame(compact('old', 'new'), $value);
            }

            $update3 = [
                'distinctAttribute'    => null,
                'searchableAttributes' => null,
                'displayedAttributes'  => null,
                'stopWords'            => $settings['stopWords'],
            ];
            $changes = ($action)(self::INDEX, $update3);
            $this->assertCount(3, $changes);

            foreach ($changes as $key => $value) {
                $old = $key === 'stopWords' ? $defaults[$key] : $settings[$key];
                $new = $update3[$key];
                $this->assertSame(compact('old', 'new'), $value);
            }

            $update4 = [
                'displayedAttributes'  => null,
                'distinctAttribute'    => null,
                'filterableAttributes' => null,
                'rankingRules'         => null,
                'searchableAttributes' => null,
                'sortableAttributes'   => null,
                'stopWords'            => null,
                'synonyms'             => null,
            ];
            $changes = ($action)(self::INDEX, $update4);
            $this->assertCount(3, $changes);

            foreach ($changes as $key => $value) {
                $old = $settings[$key];
                $new = $update4[$key];
                $this->assertSame(compact('old', 'new'), $value);
            }

            $changes = ($action)(self::INDEX, $update4);
            $this->assertEmpty($changes);
        });
    }

    /**
     * Test SynchronizesIndex::__invoke() method with typo tolerance settings.
     */
    public function test_with_typo_tolerance_settings(): void
    {
        // Check if test should be run on this engine version.
        $version = Helpers::engineVersion() ?: '0.0.0';
        if (version_compare($version, '0.27.0', '<')) {
            $this->markTestSkipped('Typo tolerance is only available from 0.27.0 and up.');
        }

        $this->withIndex(self::INDEX, function () use ($version) {
            $action = $this->app->make(SynchronizesIndex::class);

            // Grab default settings.
            $defaults = Helpers::defaultSettings($version);
            $default = $defaults['typoTolerance'];

            $update = ['enabled' => false];
            $current = Arr::only($default, array_keys($update));
            $updates = ['typoTolerance' => $update];
            $expected = ['typoTolerance' => ['old' => $current, 'new' => $update]];
            $changes = ($action)(self::INDEX, $updates);
            $this->assertCount(1, $changes);
            $this->assertCount(1, $changes['typoTolerance']['old']);
            $this->assertCount(1, $changes['typoTolerance']['new']);
            $this->assertSame($expected, $changes);

            // Attempting to do the same updates should result in zero changes.
            $changes = ($action)(self::INDEX, $updates);
            $this->assertCount(0, $changes);

            $default = array_replace($default, $update);
            $update = [
                'enabled'             => null,
                'minWordSizeForTypos' => [
                    'oneTypo'  => 2,
                    'twoTypos' => 6,
                ],
            ];
            $current = Arr::only($default, array_keys($update));
            $updates = ['typoTolerance' => $update];
            $expected = ['typoTolerance' => ['old' => $current, 'new' => $update]];
            $changes = ($action)(self::INDEX, $updates);
            $this->assertCount(1, $changes);
            $this->assertCount(2, $changes['typoTolerance']['old']);
            $this->assertCount(2, $changes['typoTolerance']['new']);
            $this->assertSame($expected, $changes);

            // Attempting to do the same updates should result in zero changes.
            $changes = ($action)(self::INDEX, $updates);
            $this->assertCount(0, $changes);

            $default = array_replace($default, $update, Arr::only($defaults['typoTolerance'], 'enabled'));
            $update = [
                'minWordSizeForTypos' => null,
                'disableOnWords'      => ['title', 'rank'],
            ];
            $current = Arr::only($default, array_keys($update));
            $updates = ['typoTolerance' => $update];
            sort($update['disableOnWords']); // List is sorted automatically before update.
            $expected = ['typoTolerance' => ['old' => $current, 'new' => $update]];
            $changes = ($action)(self::INDEX, $updates);
            $this->assertCount(1, $changes);
            $this->assertCount(2, $changes['typoTolerance']['old']);
            $this->assertCount(2, $changes['typoTolerance']['new']);
            $this->assertSame($expected, $changes);

            // Attempting to do the same updates should result in zero changes.
            $changes = ($action)(self::INDEX, $updates);
            $this->assertCount(0, $changes);

            $default = array_replace($default, $update, Arr::only($defaults['typoTolerance'], 'minWordSizeForTypos'));
            $update = [
                'disableOnWords'      => null,
                'disableOnAttributes' => ['title', 'rank'],
            ];
            $current = Arr::only($default, array_keys($update));
            $updates = ['typoTolerance' => $update];
            sort($update['disableOnAttributes']); // List is sorted automatically before update.
            $expected = ['typoTolerance' => ['old' => $current, 'new' => $update]];
            $changes = ($action)(self::INDEX, $updates);
            $this->assertCount(1, $changes);
            $this->assertCount(2, $changes['typoTolerance']['old']);
            $this->assertCount(2, $changes['typoTolerance']['new']);
            $this->assertSame($expected, $changes);

            // Attempting to do the same updates should result in zero changes.
            $changes = ($action)(self::INDEX, $updates);
            $this->assertCount(0, $changes);

            $default = Arr::only($update, 'disableOnAttributes');
            $update = [
                'minWordSizeForTypos' => null,
                'disableOnWords'      => null,
                'disableOnAttributes' => null,
            ];
            $current = Arr::only($default, array_keys($update));
            $updates = ['typoTolerance' => $update];
            $expected = ['typoTolerance' => ['old' => $current, 'new' => Arr::only($update, 'disableOnAttributes')]];
            $changes = ($action)(self::INDEX, $updates);
            $this->assertCount(1, $changes);
            $this->assertCount(1, $changes['typoTolerance']['old']);
            $this->assertCount(1, $changes['typoTolerance']['new']);
            $this->assertSame($expected, $changes);

            // Attempting to do the same updates should result in zero changes.
            $changes = ($action)(self::INDEX, $updates);
            $this->assertCount(0, $changes);
        });
    }

    /**
     * Test SynchronizesIndex::__invoke() method with pretend.
     */
    public function test_with_pretend(): void
    {
        $this->withIndex(self::INDEX, function () {
            $action = $this->app->make(SynchronizesIndex::class);

            $changes = ($action)(self::INDEX, []);
            $this->assertSame([], $changes);

            $defaults = Helpers::defaultSettings(Helpers::engineVersion());
            $settings = Tools::movieSettings();

            $changes = ($action)(self::INDEX, $settings, true);
            $this->assertCount(8, $changes);

            foreach ($changes as $key => $value) {
                $old = $defaults[$key];
                $new = $settings[$key];
                $this->assertSame(compact('old', 'new'), $value);
            }

            $update = [
                'displayedAttributes'  => null,
                'distinctAttribute'    => null,
                'filterableAttributes' => null,
                'rankingRules'         => null,
                'searchableAttributes' => null,
                'sortableAttributes'   => null,
                'stopWords'            => null,
                'synonyms'             => null,
            ];
            $changes = ($action)(self::INDEX, $update);
            $this->assertEmpty($changes);
        });
    }
}
