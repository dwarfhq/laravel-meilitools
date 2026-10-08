<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Console\Commands\Concerns\RequiresIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesScoutIndex;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;

class IndexSynchronize extends Command
{
    use RequiresIndex;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:index:synchronize
                            {index? : Index name}
                            {--P|pretend : Only shows what changes would have been done to the index}';

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
        $changes = $synchronizeScoutIndex($this->getIndex(), (bool) $this->option('pretend'));
        $values = Helpers::convertIndexChangesToTable($changes);

        $this->table(['Setting', 'Old', 'New'], $values);

        return Command::SUCCESS;
    }
}
