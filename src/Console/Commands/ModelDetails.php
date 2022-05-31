<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands;

use Dwarf\MeiliTools\Contracts\Actions\DetailsModelIndex;
use Dwarf\MeiliTools\Helpers;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ModelDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:model:details {model? : Model class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get details for a MeiliSearch model index';

    /**
     * Execute the console command.
     *
     * @param \Dwarf\MeiliTools\Contracts\Actions\DetailsModelIndex $detailModelIndex
     *
     * @return int
     */
    public function handle(DetailsModelIndex $detailModelIndex)
    {
        $details = $detailModelIndex($this->getModel());
        $values = collect($details)
            ->map(function ($value, $setting) {
                return [
                    (string) Str::of($setting)->snake()->replace('_', ' ')->title(),
                    Helpers::export($value),
                ];
            })
            ->values()
            ->all()
        ;

        $this->table(['Setting', 'Value'], $values);

        return Command::SUCCESS;
    }

    /**
     * Get model class.
     *
     * @return string
     */
    protected function getModel(): string
    {
        return $this->argument('model') ?? $this->ask('What is the model class?');
    }
}
