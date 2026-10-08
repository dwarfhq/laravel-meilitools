<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\ListsModels;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModels;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Throwable;

class ModelsSynchronize extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:models:synchronize
                            {--P|pretend : Only shows what changes would have been done to the indexes}
                            {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize all models with MeiliSearch index settings';

    /**
     * Execute the console command.
     */
    public function handle(ListsModels $listModels, SynchronizesModels $synchronizeModels): int
    {
        $pretend = (bool) $this->option('pretend');
        if (!$pretend && !$this->confirmToProceed()) {
            return Command::FAILURE;
        }

        $failed = false;
        $synchronizeModels($listModels(), function (string $class, array|Throwable $result) use (&$failed): void {
            $this->info('Processed ' . $class);
            if (\is_array($result)) {
                $this->table(['Setting', 'Old', 'New'], Helpers::convertIndexChangesToTable($result));
            } else {
                $failed = true;
                $this->error(\sprintf("Exception '%s' with message '%s'", $result::class, $result->getMessage()));
            }
        }, $pretend);

        return $failed ? Command::FAILURE : Command::SUCCESS;
    }
}
