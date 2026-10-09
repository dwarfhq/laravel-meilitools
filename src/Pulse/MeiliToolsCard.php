<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Pulse;

use Dwarf\MeiliTools\Contracts\Actions\ChecksHealth;
use Dwarf\MeiliTools\Contracts\Actions\ViewsStats;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\View;
use Laravel\Pulse\Livewire\Card;
use Laravel\Pulse\Livewire\Concerns\HasPeriod;
use Laravel\Pulse\Livewire\Concerns\RemembersQueries;
use Livewire\Attributes\Lazy;
use Throwable;

/**
 * Laravel Pulse card showing the health and indexes of MeiliSearch.
 */
#[Lazy]
class MeiliToolsCard extends Card
{
    use HasPeriod;
    use RemembersQueries;

    /**
     * Minutes within which failed tasks are shown.
     */
    public int $failedTasksWithinMinutes = 60;

    /**
     * Seconds the MeiliSearch information is cached.
     */
    public int $ttl = 60;

    public function render(): Renderable
    {
        [$data, $time, $runAt] = $this->remember(fn (): array => $this->data(), 'meilitools', $this->ttl);

        return View::file(__DIR__ . '/../../resources/views/pulse/card.blade.php', [
            ...$data,
            'time'  => $time,
            'runAt' => $runAt,
        ]);
    }

    /**
     * Get the health report and index stats.
     *
     * @return array{health: array<string, mixed>, indexes: array<string, array<string, mixed>>}
     */
    protected function data(): array
    {
        $health = resolve(ChecksHealth::class)($this->failedTasksWithinMinutes);

        try {
            /** @var array<string, array<string, mixed>> $indexes */
            $indexes = $health['version'] === null ? [] : resolve(ViewsStats::class)()['indexes'] ?? [];
        } catch (Throwable) {
            $indexes = [];
        }
        ksort($indexes);

        return ['health' => $health, 'indexes' => $indexes];
    }
}
