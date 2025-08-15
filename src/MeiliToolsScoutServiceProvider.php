<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools;

use Dwarf\MeiliTools\Actions\DetailModel;
use Dwarf\MeiliTools\Actions\ResetModel;
use Dwarf\MeiliTools\Actions\SynchronizeModel;
use Dwarf\MeiliTools\Actions\SynchronizeModels;
use Dwarf\MeiliTools\Actions\ViewModel;
use Dwarf\MeiliTools\Console\Commands\ModelDetails;
use Dwarf\MeiliTools\Console\Commands\ModelReset;
use Dwarf\MeiliTools\Console\Commands\ModelsSynchronize;
use Dwarf\MeiliTools\Console\Commands\ModelSynchronize;
use Dwarf\MeiliTools\Console\Commands\ModelView;
use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\ResetsModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModels;
use Dwarf\MeiliTools\Contracts\Actions\ViewsModel;
use Illuminate\Support\ServiceProvider;

class MeiliToolsScoutServiceProvider extends ServiceProvider
{
    /**
     * Actions to bind.
     */
    public array $bindings = [
        DetailsModel::class       => DetailModel::class,
        ResetsModel::class        => ResetModel::class,
        SynchronizesModel::class  => SynchronizeModel::class,
        SynchronizesModels::class => SynchronizeModels::class,
        ViewsModel::class         => ViewModel::class,
    ];

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ModelDetails::class,
                ModelReset::class,
                ModelSynchronize::class,
                ModelView::class,
                ModelsSynchronize::class,
            ]);
        }
    }
}
