<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Console\Commands\Concerns\ChecksSynchronization;
use Dwarf\MeiliTools\Console\Commands\Concerns\RequiresIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesScoutIndex;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;

class IndexSynchronize extends Command
{
    use ChecksSynchronization;
    use RequiresIndex;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:index:synchronize
                            {index? : Index name}
                            {--P|pretend : Only shows what changes would have been done to the index}
                            {--check : Only checks whether the settings are in sync, failing when they are not}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize settings for a MeiliSearch index configured in Scout';

    /**
     * Execute the console command.
     */
    public function handle(SynchronizesScoutIndex $synchronizeScoutIndex): int
    {
        $changes = $synchronizeScoutIndex($this->getIndex(), $this->pretending());
        $values = Helpers::convertIndexChangesToTable($changes);

        $this->table(['Setting', 'Old', 'New'], $values);

        if ($this->checking() && $changes !== []) {
            $this->error('Settings are out of sync');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
