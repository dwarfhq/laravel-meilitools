<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\ViewsIndex;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;

class IndexView extends Command
{
    use Concerns\RequiresIndex;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:index:view {index? : Index name} {--S|stats : Whether to include index stats}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get base information about a MeiliSearch index';

    /**
     * Execute the console command.
     *
     * @param \Dwarf\MeiliTools\Contracts\Actions\ViewsIndex $viewIndex
     *
     * @return int
     */
    public function handle(ViewsIndex $viewIndex)
    {
        $info = $viewIndex($this->getIndex(), $this->option('stats'));
        $values = Helpers::convertIndexDataToTable($info);

        $this->table(['Key', 'Value'], $values);

        return Command::SUCCESS;
    }
}
