<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\SynchronizesScoutIndex;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Throwable;

class IndexesSynchronize extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:indexes:synchronize
                            {--P|pretend : Only shows what changes would have been done to the indexes}
                            {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize all MeiliSearch indexes configured in Scout which are not bound to a model';

    /**
     * Execute the console command.
     */
    public function handle(SynchronizesScoutIndex $synchronizeScoutIndex): int
    {
        $pretend = (bool) $this->option('pretend');
        if (!$pretend && !$this->confirmToProceed()) {
            return Command::FAILURE;
        }

        foreach (array_keys(Helpers::scoutIndexes()) as $index) {
            $this->info('Processed ' . $index);

            try {
                $changes = $synchronizeScoutIndex($index, $pretend);
                $this->table(['Setting', 'Old', 'New'], Helpers::convertIndexChangesToTable($changes));
            } catch (Throwable $e) {
                $this->error(\sprintf("Exception '%s' with message '%s'", $e::class, $e->getMessage()));
            }
        }

        return Command::SUCCESS;
    }
}
