<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Feature\Actions;

use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\TestCase;

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
     * Test SynchronizesIndex::__invoke() method.
     *
     * @return void
     */
    public function testInvoke(): void
    {
        $this->withIndex(self::INDEX, function () {
            $action = $this->app->make(SynchronizesIndex::class);

            $changes = ($action)(self::INDEX, []);
            $this->assertSame([], $changes);

            $defaults = Helpers::defaultSettings();
            $settings = $this->getMovieSettings();

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
        });
    }
}
