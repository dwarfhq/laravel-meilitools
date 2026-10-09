<?php

declare(strict_types=1);

use Dwarf\MeiliTools\Contracts\Actions\ChecksHealth;
use Dwarf\MeiliTools\Health\MeiliToolsCheck;

/**
 * Bind a health action returning the given report.
 *
 * @param array<string, mixed> $report
 */
function fakeHealth(array $report): void
{
    app()->instance(ChecksHealth::class, new class($report) implements ChecksHealth
    {
        /**
         * @param array<string, mixed> $report
         */
        public function __construct(public array $report)
        {
        }

        public function __invoke(int $failedTasksWithinMinutes = 60, bool $checkSettings = true): array
        {
            return $this->report + [
                'failedTasksWithinMinutes' => $failedTasksWithinMinutes,
                'checkSettings'            => $checkSettings,
            ];
        }
    });
}

/**
 * Test the health check results.
 */
test('check', function (array $report, string $status, string $summary, string $message): void {
    fakeHealth($report + [
        'version'         => '1.54.3',
        'error'           => null,
        'missingIndexes'  => [],
        'outOfSync'       => [],
        'errors'          => [],
        'failedTasks'     => [],
        'failedTaskCount' => 0,
    ]);

    $result = MeiliToolsCheck::new()->failedTasksWithin(30)->checkSettings(false)->run();

    expect($result->status->value)->toBe($status)
        ->and($result->getShortSummary())->toBe($summary)
        ->and($result->getNotificationMessage())->toBe($message)
    ;
})->with([
    'ok'          => [[], 'ok', '1.54.3', ''],
    'unreachable' => [['version' => null], 'failed', 'Unreachable', 'MeiliSearch could not be reached.'],
    'error'       => [
        ['error' => 'The provided API key is invalid.'],
        'failed',
        'Failed',
        'MeiliSearch could not be checked: The provided API key is invalid.',
    ],
    'missing' => [
        ['missingIndexes' => ['books', 'authors'], 'outOfSync' => ['movies' => ['rankingRules']]],
        'failed',
        'Failed',
        'Missing indexes: books, authors. Indexes with settings out of sync: movies.',
    ],
    'errors' => [
        ['errors' => ['movies' => 'Invalid']],
        'failed',
        'Failed',
        'Indexes failing to synchronize: movies.',
    ],
    'warnings' => [
        ['outOfSync' => ['movies' => ['rankingRules']], 'failedTasks' => [['uid' => 1]], 'failedTaskCount' => 25],
        'warning',
        'Warning',
        'Indexes with settings out of sync: movies. 25 tasks failed within 30 minutes.',
    ],
    'one failed task' => [
        ['failedTasks' => [['uid' => 1]], 'failedTaskCount' => 1],
        'warning',
        'Warning',
        '1 task failed within 30 minutes.',
    ],
]);
