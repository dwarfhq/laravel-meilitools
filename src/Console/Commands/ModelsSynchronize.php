<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Console\Commands\Concerns\ChecksSynchronization;
use Dwarf\MeiliTools\Contracts\Actions\ListsModels;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModels;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Throwable;

class ModelsSynchronize extends Command
{
    use ChecksSynchronization;
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:models:synchronize
                            {--P|pretend : Only shows what changes would have been done to the indexes}
                            {--check : Only checks whether the settings are in sync, failing when they are not}
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
        $pretend = $this->pretending();
        if (!$pretend && !$this->confirmToProceed()) {
            return Command::FAILURE;
        }

        $failed = false;
        $outOfSync = 0;
        $report = function (string $class, array|Throwable $result) use (&$failed, &$outOfSync): void {
            $this->info('Processed ' . $class);
            if (\is_array($result)) {
                $outOfSync += $result === [] ? 0 : 1;
                $this->table(['Setting', 'Old', 'New'], Helpers::convertIndexChangesToTable($result));
            } else {
                $failed = true;
                $this->error(\sprintf("Exception '%s' with message '%s'", $result::class, $result->getMessage()));
            }
        };

        $synchronizeModels($listModels(), $report, $pretend);

        $outOfSync = $this->checking() ? $outOfSync : 0;
        if ($outOfSync > 0) {
            $noun = $outOfSync === 1 ? 'model' : 'models';
            $this->error(\sprintf('Settings are out of sync for %d %s', $outOfSync, $noun));
        }

        return $failed || $outOfSync > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
