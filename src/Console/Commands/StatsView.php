<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\ViewsStats;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class StatsView extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:stats {index? : Index name, showing the stats of all indexes when omitted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the stats of MeiliSearch or a MeiliSearch index';

    /**
     * Execute the console command.
     */
    public function handle(ViewsStats $viewStats): int
    {
        $index = $this->argument('index');
        $stats = $viewStats(\is_string($index) ? $index : null);

        /** @var array<string, int> $fields */
        $fields = $stats['fieldDistribution'] ?? [];
        /** @var array<string, array<string, mixed>> $indexes */
        $indexes = $stats['indexes'] ?? [];
        unset($stats['fieldDistribution'], $stats['indexes']);

        $this->table(['Stat', 'Value'], array_map(
            fn (string $key, mixed $value): array => [Str::headline($key), $this->formatStat($key, $value)],
            array_keys($stats),
            array_values($stats),
        ));

        if ($fields !== []) {
            ksort($fields);
            $this->table(['Field', 'Documents'], array_map(null, array_keys($fields), array_values($fields)));
        }

        if ($indexes !== []) {
            ksort($indexes);
            $this->table(['Index', 'Documents', 'Indexing', 'Size'], array_map(
                fn (string $uid, array $index): array => [
                    $uid,
                    $this->formatStat('numberOfDocuments', $index['numberOfDocuments'] ?? null),
                    $this->formatStat('isIndexing', $index['isIndexing'] ?? null),
                    $this->formatStat('indexSize', $index['indexSize'] ?? null),
                ],
                array_keys($indexes),
                array_values($indexes),
            ));
        }

        return Command::SUCCESS;
    }

    /**
     * Format a stat value, with sizes in bytes made readable.
     */
    protected function formatStat(string $key, mixed $value): string
    {
        return match (true) {
            $value === null                                   => '-',
            \is_bool($value)                                  => $value ? 'Yes' : 'No',
            Str::endsWith($key, 'Size') && is_numeric($value) => Helpers::formatBytes(+$value),
            \is_scalar($value)                                => (string) $value,
            default                                           => Helpers::export($value),
        };
    }
}
