<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;

class IndexesList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:indexes:list {--S|stats : Whether to include index stats}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all MeiliSearch indexes';

    /**
     * Execute the console command.
     *
     * @param \Dwarf\MeiliTools\Contracts\Actions\ListsIndexes $listIndexes
     *
     * @return int
     */
    public function handle(ListsIndexes $listIndexes)
    {
        $list = $listIndexes($this->option('stats'));
        $values = Helpers::convertIndexDataToTable($list);

        $this->table(['Index', 'Data'], $values);

        return Command::SUCCESS;
    }
}
