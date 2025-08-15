<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;

class IndexDetails extends Command
{
    use Concerns\RequiresIndex;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:index:details {index? : Index name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get details for a MeiliSearch index';

    /**
     * Execute the console command.
     *
     *
     * @return int
     */
    public function handle(DetailsIndex $detailIndex)
    {
        $details = $detailIndex($this->getIndex());
        $values = Helpers::convertIndexDataToTable($details);

        $this->table(['Setting', 'Value'], $values);

        return Command::SUCCESS;
    }
}
