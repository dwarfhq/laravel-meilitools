<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\ListsModels;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesScoutIndexes;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Throwable;

class IndexesSynchronize extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:indexes:synchronize
                            {--P|pretend : Only shows what changes would have been done to the indexes}
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
        $pretend = (bool) $this->option('pretend');
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
        $synchronizeScoutIndexes($indexes, function (string $index, array|Throwable $result) use (&$failed): void {
            $this->info('Processed ' . $index);
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
