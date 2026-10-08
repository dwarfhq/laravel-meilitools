<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Console\Commands\Concerns\RequiresIndex;
use Dwarf\MeiliTools\Contracts\Actions\DeletesIndex;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class IndexDelete extends Command
{
    use ConfirmableTrait;
    use RequiresIndex;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:index:delete
                            {index? : Index name}
                            {--force : Force the operation to run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a MeiliSearch index';

    /**
     * Execute the console command.
     */
    public function handle(DeletesIndex $deleteIndex): int
    {
        $index = $this->getIndex();
        if (!$this->confirmToProceed("Index '{$index}' is about to be deleted", fn (): true => true)) {
            return Command::FAILURE;
        }

        $deleteIndex($index);

        return Command::SUCCESS;
    }
}
