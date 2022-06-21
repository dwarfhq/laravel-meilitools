<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;

class ModelSynchronize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:model:synchronize
                            {model? : Model class}
                            {--pretend : Only shows what changes would have been done to the index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize settings for a MeiliSearch model index';

    /**
     * Execute the console command.
     *
     * @param \Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel $synchronizeModel
     *
     * @return int
     */
    public function handle(SynchronizesModel $synchronizeModel)
    {
        $changes = $synchronizeModel($this->getModel(), $this->option('pretend'));
        $values = Helpers::convertIndexChangesToTable($changes);

        $this->table(['Setting', 'Old', 'New'], $values);

        return Command::SUCCESS;
    }

    /**
     * Get model class.
     *
     * @return string
     */
    protected function getModel(): string
    {
        return $this->argument('model') ?? $this->ask('What is the model class?');
    }
}
