<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Console\Commands\Concerns\ChecksSynchronization;
use Dwarf\MeiliTools\Console\Commands\Concerns\RequiresModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;

class ModelSynchronize extends Command
{
    use ChecksSynchronization;
    use RequiresModel;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:model:synchronize
                            {model? : Model class}
                            {--P|pretend : Only shows what changes would have been done to the index}
                            {--check : Only checks whether the settings are in sync, failing when they are not}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize settings for a MeiliSearch model index';

    /**
     * Execute the console command.
     */
    public function handle(SynchronizesModel $synchronizeModel): int
    {
        $changes = $synchronizeModel($this->getModel(), $this->pretending());
        $values = Helpers::convertIndexChangesToTable($changes);

        $this->table(['Setting', 'Old', 'New'], $values);

        if ($this->checking() && $changes !== []) {
            $this->error('Settings are out of sync');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
