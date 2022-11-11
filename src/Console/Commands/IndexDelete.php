<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\DeletesIndex;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;

class IndexDelete extends Command
{
    use Concerns\RequiresIndex;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:index:delete {index? : Index name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a MeiliSearch index';

    /**
     * Execute the console command.
     *
     * @param  \Dwarf\MeiliTools\Contracts\Actions\DeletesIndex  $deleteIndex
     * @return int
     */
    public function handle(DeletesIndex $deleteIndex)
    {
        $index = $this->getIndex();

        if (! $this->confirm(__('Are you sure you wish to permanently delete the :index index?', ['index' => $index]))) {
            return Command::FAILURE;
        }

        $details = $deleteIndex($index);
        $values = Helpers::convertIndexDataToTable($details);

        $this->table(['Setting', 'Value'], $values);

        return Command::SUCCESS;
    }
}
