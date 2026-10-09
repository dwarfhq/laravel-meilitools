<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\CancelsTasks;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class TasksCancel extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:tasks:cancel
                            {--status=* : Only cancel tasks with the status, enqueued or processing}
                            {--type=* : Only cancel tasks of the type, e.g. documentAdditionOrUpdate}
                            {--index=* : Only cancel tasks of the index}
                            {--uid=* : Only cancel the task with the uid}
                            {--force : Force the operation to run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel enqueued and processing MeiliSearch tasks';

    /**
     * Execute the console command.
     */
    public function handle(CancelsTasks $cancelTasks): int
    {
        if (!$this->confirmToProceed('Matching tasks are about to be canceled', fn (): true => true)) {
            return Command::FAILURE;
        }

        $canceled = $cancelTasks([
            'statuses'  => array_values(array_map(strval(...), (array) $this->option('status'))),
            'types'     => array_values(array_map(strval(...), (array) $this->option('type'))),
            'indexUids' => array_values(array_map(strval(...), (array) $this->option('index'))),
            'uids'      => array_values(array_map(intval(...), (array) $this->option('uid'))),
        ]);

        $this->info(\sprintf('Canceled %d %s', $canceled, $canceled === 1 ? 'task' : 'tasks'));

        return Command::SUCCESS;
    }
}
