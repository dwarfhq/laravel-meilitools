<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;

class ModelDetails extends Command
{
    use HasModelTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:model:details {model? : Model class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get details for a MeiliSearch model index';

    /**
     * Execute the console command.
     *
     * @param \Dwarf\MeiliTools\Contracts\Actions\DetailsModel $detailModel
     *
     * @return int
     */
    public function handle(DetailsModel $detailModel)
    {
        $details = $detailModel($this->getModel());
        $values = Helpers::convertIndexSettingsToTable($details);

        $this->table(['Setting', 'Value'], $values);

        return Command::SUCCESS;
    }
}
