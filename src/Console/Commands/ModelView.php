<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\ViewsModel;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;

class ModelView extends Command
{
    use Concerns\RequiresModel;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:model:view {model? : Model class} {--S|stats : Whether to include index stats}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get base information about a MeiliSearch model index';

    /**
     * Execute the console command.
     */
    public function handle(ViewsModel $viewModel): int
    {
        $info = $viewModel($this->getModel(), $this->option('stats'));
        $values = Helpers::convertIndexDataToTable($info);

        $this->table(['Key', 'Value'], $values);

        return Command::SUCCESS;
    }
}
