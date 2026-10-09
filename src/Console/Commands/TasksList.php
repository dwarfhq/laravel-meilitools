<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Dwarf\MeiliTools\Contracts\Actions\ListsTasks;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TasksList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:tasks
                            {--status=* : Only list tasks with the status, e.g. failed}
                            {--type=* : Only list tasks of the type, e.g. documentAdditionOrUpdate}
                            {--index=* : Only list tasks of the index}
                            {--limit=20 : Maximum number of tasks to list}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List the most recent MeiliSearch tasks';

    /**
     * Execute the console command.
     */
    public function handle(ListsTasks $listTasks): int
    {
        $tasks = $listTasks([
            'statuses'  => array_values(array_map(strval(...), (array) $this->option('status'))),
            'types'     => array_values(array_map(strval(...), (array) $this->option('type'))),
            'indexUids' => array_values(array_map(strval(...), (array) $this->option('index'))),
        ], max(1, (int) $this->option('limit')));

        $this->table(
            ['Uid', 'Index', 'Type', 'Status', 'Enqueued', 'Duration', 'Error'],
            array_map($this->formatTask(...), $tasks),
        );

        return Command::SUCCESS;
    }

    /**
     * Format a task as a table row.
     *
     * @param array<string, mixed> $task
     *
     * @return list<mixed>
     */
    protected function formatTask(array $task): array
    {
        $enqueuedAt = $task['enqueuedAt'] ?? null;
        $duration = $task['duration'] ?? null;
        $error = \is_array($task['error'] ?? null) ? (string) ($task['error']['message'] ?? '') : '';

        return [
            $task['uid'] ?? '',
            $task['indexUid'] ?? '',
            $task['type'] ?? '',
            $task['status'] ?? '',
            \is_string($enqueuedAt) ? CarbonImmutable::parse($enqueuedAt)->format('Y-m-d H:i:s') : '',
            \is_string($duration) ? $this->formatDuration($duration) : '',
            Str::limit($error, 80),
        ];
    }

    /**
     * Format an ISO 8601 duration, e.g. `PT0.024S` as `24 ms`.
     */
    protected function formatDuration(string $duration): string
    {
        if (preg_match('/^P(?:(\d+)D)?T?(?:(\d+)H)?(?:(\d+)M)?(?:([\d.]+)S)?$/', $duration, $matches) !== 1) {
            return $duration;
        }

        $seconds = (int) ($matches[1] ?? 0) * 86400
            + (int) ($matches[2] ?? 0) * 3600
            + (int) ($matches[3] ?? 0) * 60
            + (float) ($matches[4] ?? 0);

        return match (true) {
            $seconds < 1  => \sprintf('%d ms', round($seconds * 1000)),
            $seconds < 60 => \sprintf('%s s', round($seconds, 1)),
            default       => CarbonInterval::seconds((int) $seconds)->cascade()->forHumans(short: true),
        };
    }
}
