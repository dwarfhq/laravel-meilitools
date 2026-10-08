<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;

class IndexesList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:indexes:list {--S|stats : Whether to include index stats}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all MeiliSearch indexes';

    /**
     * Execute the console command.
     */
    public function handle(ListsIndexes $listIndexes): int
    {
        $list = $listIndexes((bool) $this->option('stats'));
        $values = array_map(
            fn (string $index, array $data): array => [$index, Helpers::export($data)],
            array_keys($list),
            array_values($list),
        );

        $this->table(['Index', 'Data'], $values);

        return Command::SUCCESS;
    }
}
