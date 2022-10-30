<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools;

use Dwarf\MeiliTools\Actions\DetailIndex;
use Dwarf\MeiliTools\Actions\DetailModel;
use Dwarf\MeiliTools\Actions\EnsureIndexExists;
use Dwarf\MeiliTools\Actions\ListClasses;
use Dwarf\MeiliTools\Actions\ListIndexes;
use Dwarf\MeiliTools\Actions\ResetIndex;
use Dwarf\MeiliTools\Actions\ResetModel;
use Dwarf\MeiliTools\Actions\SynchronizeIndex;
use Dwarf\MeiliTools\Actions\SynchronizeModel;
use Dwarf\MeiliTools\Actions\SynchronizeModels;
use Dwarf\MeiliTools\Actions\ValidateIndexSettings;
use Dwarf\MeiliTools\Actions\ViewIndex;
use Dwarf\MeiliTools\Console\Commands\IndexDetails;
use Dwarf\MeiliTools\Console\Commands\IndexesList;
use Dwarf\MeiliTools\Console\Commands\IndexReset;
use Dwarf\MeiliTools\Console\Commands\IndexView;
use Dwarf\MeiliTools\Console\Commands\ModelDetails;
use Dwarf\MeiliTools\Console\Commands\ModelReset;
use Dwarf\MeiliTools\Console\Commands\ModelsSynchronize;
use Dwarf\MeiliTools\Console\Commands\ModelSynchronize;
use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Contracts\Actions\ListsClasses;
use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Contracts\Actions\ResetsIndex;
use Dwarf\MeiliTools\Contracts\Actions\ResetsModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModels;
use Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings;
use Dwarf\MeiliTools\Contracts\Actions\ViewsIndex;
use Dwarf\MeiliTools\Contracts\Rules\ArrayAssocRule;
use Dwarf\MeiliTools\Rules\ArrayAssoc;
use Illuminate\Support\ServiceProvider;

class MeiliToolsServiceProvider extends ServiceProvider
{
    /**
     * Actions to bind.
     *
     * @var array
     */
    public array $bindings = [
        ArrayAssocRule::class         => ArrayAssoc::class,
        DetailsIndex::class           => DetailIndex::class,
        DetailsModel::class           => DetailModel::class,
        EnsuresIndexExists::class     => EnsureIndexExists::class,
        ListsClasses::class           => ListClasses::class,
        ListsIndexes::class           => ListIndexes::class,
        ViewsIndex::class             => ViewIndex::class,
        ResetsIndex::class            => ResetIndex::class,
        ResetsModel::class            => ResetModel::class,
        SynchronizesIndex::class      => SynchronizeIndex::class,
        SynchronizesModel::class      => SynchronizeModel::class,
        SynchronizesModels::class     => SynchronizeModels::class,
        ValidatesIndexSettings::class => ValidateIndexSettings::class,
    ];

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/meilitools.php', 'meilitools');
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                IndexDetails::class,
                IndexReset::class,
                IndexView::class,
                IndexesList::class,
                ModelDetails::class,
                ModelReset::class,
                ModelSynchronize::class,
                ModelsSynchronize::class,
            ]);

            $this->publishes([__DIR__ . '/../config/meilitools.php' => $this->app['path.config'] . '/meilitools.php']);
        }
    }
}
