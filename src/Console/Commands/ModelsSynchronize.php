<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\ListsClasses;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModels;
use Dwarf\MeiliTools\Contracts\Indexes\MeiliSettings;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

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
    protected $description = 'Synchronize all models implementing MeiliSearch index settings';

    /**
     * Execute the console command.
     */
    public function handle(ListsClasses $listClasses, SynchronizesModels $synchronizeModels): int
    {
        // Confirm execution if not pretending and in production.
        if (!$this->option('pretend') && !$this->confirmToProceed()) {
            return Command::FAILURE;
        }

        $paths = config('meilitools.paths');
        $classes = collect($paths)
            ->map(function ($namespace, $path) use ($listClasses) {
                return $listClasses($path, $namespace, fn ($class) => is_a($class, MeiliSettings::class, true));
            })
            ->values()
            ->flatten()
            ->all()
        ;

        if (!empty($classes)) {
            $synchronizeModels($classes, function ($class, $result) {
                $this->info('Processed ' . $class);
                if (\is_array($result)) {
                    $changes = Helpers::convertIndexChangesToTable($result);
                    $this->table(['Setting', 'Old', 'New'], $changes);
                } else {
                    $error = sprintf("Exception '%s' with message '%s'", \get_class($result), $result->getMessage());
                    $this->error($error);
                }
            }, $this->option('pretend'));
        }

        return Command::SUCCESS;
    }
}
