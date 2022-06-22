<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\ResetsIndex;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;

class IndexReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:index:reset
                            {index? : Index name}
                            {--pretend : Only shows what changes would have been done to the index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset settings for a MeiliSearch index';

    /**
     * Execute the console command.
     *
     * @param \Dwarf\MeiliTools\Contracts\Actions\ResetsIndex $resetIndex
     *
     * @return int
     */
    public function handle(ResetsIndex $resetIndex)
    {
        $changes = $resetIndex($this->getIndex(), $this->option('pretend'));
        $values = Helpers::convertIndexChangesToTable($changes);

        $this->table(['Setting', 'Old', 'New'], $values);

        return Command::SUCCESS;
    }

    /**
     * Get index name.
     *
     * @return string
     */
    protected function getIndex(): string
    {
        return $this->argument('index') ?? $this->ask('What is the index name?');
    }
}
