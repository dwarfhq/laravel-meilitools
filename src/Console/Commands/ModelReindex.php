<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Console\Commands\Concerns\RequiresModel;
use Dwarf\MeiliTools\Contracts\Actions\ReindexesModel;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Str;

class ModelReindex extends Command
{
    use ConfirmableTrait;
    use RequiresModel;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:model:reindex
                            {model? : Model class}
                            {--chunk= : Number of models imported at a time}
                            {--timeout=300 : Seconds to wait for each MeiliSearch task}
                            {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex a MeiliSearch model index without downtime';

    /**
     * Execute the console command.
     */
    public function handle(ReindexesModel $reindexModel): int
    {
        if (!$this->confirmToProceed()) {
            return Command::FAILURE;
        }

        $class = $this->getModel();
        $chunk = $this->option('chunk');

        $imported = $reindexModel(
            $class,
            is_numeric($chunk) ? (int) $chunk : null,
            fn (int $imported) => $this->line('Imported ' . $this->models($imported)),
            max(1, (int) $this->option('timeout')),
        );

        $this->info(\sprintf('Reindexed %s with %s', $class, $this->models($imported)));

        return Command::SUCCESS;
    }

    /**
     * Describe a number of models.
     */
    protected function models(int $count): string
    {
        return $count . ' ' . Str::plural('model', $count);
    }
}
