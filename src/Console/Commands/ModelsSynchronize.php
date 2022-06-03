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
                            {--dry-run : Only shows what changes would have been done to the indexes}
                            {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize all models implementing MeiliSearch index settings';

    /**
     * Execute the console command.
     *
     * @param \Dwarf\MeiliTools\Contracts\Actions\ListsClasses       $listClasses
     * @param \Dwarf\MeiliTools\Contracts\Actions\SynchronizesModels $synchronizeModels
     *
     * @return int
     */
    public function handle(ListsClasses $listClasses, SynchronizesModels $synchronizeModels)
    {
        // Confirm execution if not running dry-run and in production.
        if (!$this->option('dry-run') && !$this->confirmToProceed()) {
            return Command::SUCCESS;
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
                    $error = sprintf(
                        "Exception '%s' with message '%s' in %s:%d",
                        $result::class,
                        $result->getMessage(),
                        $result->getFile(),
                        $result->getline()
                    );
                    $this->error($error);
                }
            }, $this->option('dry-run'));
        }

        return Command::SUCCESS;
    }
}
