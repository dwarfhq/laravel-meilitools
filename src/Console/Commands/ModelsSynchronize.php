<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\ListsClasses;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModels;
use Dwarf\MeiliTools\Contracts\Indexes\MeiliSettings;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Eloquent\Model;
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
    public function handle(ListsClasses $listClasses, SynchronizesModels $synchronizeModels): int
    {
        $pretend = (bool) $this->option('pretend');
        if (!$pretend && !$this->confirmToProceed()) {
            return Command::FAILURE;
        }

        $configured = Helpers::scoutModels();
        $filter = fn (string $class): bool => is_a($class, MeiliSettings::class, true)
            || \in_array($class, $configured, true);

        /** @var array<string, string> $paths */
        $paths = config('meilitools.paths', []);
        /** @var list<class-string<Model>> $classes */
        $classes = collect($paths)
            ->flatMap(fn (string $namespace, string $path): array => $listClasses($path, $namespace, $filter))
            ->merge($configured)
            ->unique()
            ->values()
            ->all()
        ;

        $synchronizeModels($classes, function (string $class, array|Throwable $result): void {
            $this->info('Processed ' . $class);
            if (\is_array($result)) {
                $this->table(['Setting', 'Old', 'New'], Helpers::convertIndexChangesToTable($result));
            } else {
                $this->error(\sprintf("Exception '%s' with message '%s'", $result::class, $result->getMessage()));
            }
        }, $pretend);

        return Command::SUCCESS;
    }
}
