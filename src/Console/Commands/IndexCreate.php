<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\CreatesIndex;
use Illuminate\Console\Command;

class IndexCreate extends Command
{
    use Concerns\RequiresIndex;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:index:create {index? : Index name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a MeiliSearch index';

    /**
     * Execute the console command.
     *
     *
     * @return int
     */
    public function handle(CreatesIndex $createIndex)
    {
        $createIndex($this->getIndex());

        return Command::SUCCESS;
    }
}
