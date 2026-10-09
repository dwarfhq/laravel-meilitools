<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Console\Commands\Concerns\ChecksSynchronization;
use Dwarf\MeiliTools\Contracts\Actions\ListsModels;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesScoutIndexes;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Throwable;

class IndexesSynchronize extends Command
{
    use ChecksSynchronization;
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:indexes:synchronize
                            {--P|pretend : Only shows what changes would have been done to the indexes}
                            {--check : Only checks whether the settings are in sync, failing when they are not}
                            {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize all MeiliSearch indexes configured in Scout which are not bound to a model';

    /**
     * Execute the console command.
     */
    public function handle(ListsModels $listModels, SynchronizesScoutIndexes $synchronizeScoutIndexes): int
    {
        $pretend = $this->pretending();
        if (!$pretend && !$this->confirmToProceed()) {
            return Command::FAILURE;
        }

        // Indexes of models are synchronized with the model settings by `meili:models:synchronize`.
        $models = collect($listModels())
            ->filter(Helpers::isSearchableModel(...))
            ->mapWithKeys(fn (string $class): array => [Helpers::modelIndexName($class) => $class])
        ;

        $indexes = [];
        foreach (array_keys(Helpers::scoutIndexes()) as $index) {
            if ($models->has($index)) {
                $this->info(\sprintf('Skipped %s, synchronized by model %s', $index, $models->get($index)));
            } else {
                $indexes[] = $index;
            }
        }

        $failed = false;
        $outOfSync = 0;
        $report = function (string $index, array|Throwable $result) use (&$failed, &$outOfSync): void {
            $this->info('Processed ' . $index);
            if (\is_array($result)) {
                $outOfSync += $result === [] ? 0 : 1;
                $this->table(['Setting', 'Old', 'New'], Helpers::convertIndexChangesToTable($result));
            } else {
                $failed = true;
                $this->error(\sprintf("Exception '%s' with message '%s'", $result::class, $result->getMessage()));
            }
        };

        $synchronizeScoutIndexes($indexes, $report, $pretend);

        $outOfSync = $this->checking() ? $outOfSync : 0;
        if ($outOfSync > 0) {
            $noun = $outOfSync === 1 ? 'index' : 'indexes';
            $this->error(\sprintf('Settings are out of sync for %d %s', $outOfSync, $noun));
        }

        return $failed || $outOfSync > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
