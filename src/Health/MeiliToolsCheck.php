<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Health;

use Dwarf\MeiliTools\Contracts\Actions\ChecksHealth;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

/**
 * Spatie Laravel Health check of MeiliSearch, its indexes and recently failed tasks.
 */
class MeiliToolsCheck extends Check
{
    /**
     * Minutes within which failed tasks are reported.
     */
    protected int $failedTasksWithinMinutes = 60;

    /**
     * Whether index settings are compared.
     */
    protected bool $checkSettings = true;

    /**
     * Report tasks which failed within the given number of minutes.
     */
    public function failedTasksWithin(int $minutes): static
    {
        $this->failedTasksWithinMinutes = $minutes;

        return $this;
    }

    /**
     * Whether to check if index settings are in sync.
     */
    public function checkSettings(bool $checkSettings = true): static
    {
        $this->checkSettings = $checkSettings;

        return $this;
    }

    public function run(): Result
    {
        $report = resolve(ChecksHealth::class)($this->failedTasksWithinMinutes, $this->checkSettings);
        $result = Result::make()->meta([
            'version'        => $report['version'],
            'missingIndexes' => $report['missingIndexes'],
            'outOfSync'      => $report['outOfSync'],
            'errors'         => $report['errors'],
            'failedTasks'    => \count($report['failedTasks']),
        ]);

        if ($report['version'] === null) {
            return $result->shortSummary('Unreachable')->failed('MeiliSearch could not be reached.');
        }

        $problems = array_filter([
            'failed' => array_filter([
                $this->describe($report['missingIndexes'], 'Missing indexes: %s'),
                $this->describe(array_keys($report['errors']), 'Indexes failing to synchronize: %s'),
            ]),
            'warning' => array_filter([
                $this->describe(array_keys($report['outOfSync']), 'Indexes with settings out of sync: %s'),
                $report['failedTasks'] === [] ? null : \sprintf(
                    '%d %s failed within %d minutes.',
                    \count($report['failedTasks']),
                    \count($report['failedTasks']) === 1 ? 'task' : 'tasks',
                    $this->failedTasksWithinMinutes,
                ),
            ]),
        ]);

        if ($problems === []) {
            return $result->shortSummary($report['version'])->ok();
        }

        $message = implode(' ', array_merge(...array_values($problems)));
        $result->shortSummary(isset($problems['failed']) ? 'Failed' : 'Warning');

        return isset($problems['failed']) ? $result->failed($message) : $result->warning($message);
    }

    /**
     * Describe a list of indexes, if any.
     *
     * @param list<string> $indexes
     */
    protected function describe(array $indexes, string $format): ?string
    {
        return $indexes === [] ? null : \sprintf($format . '.', implode(', ', $indexes));
    }
}
