<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;

class IndexList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:index:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get details for all MeiliSearch indexes';

    /**
     * Execute the console command.
     *
     * @param \Dwarf\MeiliTools\Contracts\Actions\ListsIndexes $listIndexes
     *
     * @return int
     */
    public function handle(ListsIndexes $listIndexes)
    {
        $list = $listIndexes();
        $values = Helpers::convertIndexSettingsToTable($list);

        $this->table(['Setting', 'Value'], $values);

        return Command::SUCCESS;
    }
}
