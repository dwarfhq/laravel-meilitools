<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools;

use Dwarf\MeiliTools\Actions\CreateIndex;
use Dwarf\MeiliTools\Actions\DeleteIndex;
use Dwarf\MeiliTools\Actions\DetailIndex;
use Dwarf\MeiliTools\Actions\EnsureIndexExists;
use Dwarf\MeiliTools\Actions\ListClasses;
use Dwarf\MeiliTools\Actions\ListIndexes;
use Dwarf\MeiliTools\Actions\ResetIndex;
use Dwarf\MeiliTools\Actions\SynchronizeIndex;
use Dwarf\MeiliTools\Actions\ValidateIndexSettings;
use Dwarf\MeiliTools\Actions\ViewIndex;
use Dwarf\MeiliTools\Console\Commands\IndexCreate;
use Dwarf\MeiliTools\Console\Commands\IndexDelete;
use Dwarf\MeiliTools\Console\Commands\IndexDetails;
use Dwarf\MeiliTools\Console\Commands\IndexesList;
use Dwarf\MeiliTools\Console\Commands\IndexReset;
use Dwarf\MeiliTools\Console\Commands\IndexView;
use Dwarf\MeiliTools\Contracts\Actions\CreatesIndex;
use Dwarf\MeiliTools\Contracts\Actions\DeletesIndex;
use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Contracts\Actions\ListsClasses;
use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Contracts\Actions\ResetsIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings;
use Dwarf\MeiliTools\Contracts\Actions\ViewsIndex;
use Dwarf\MeiliTools\Contracts\Rules\ArrayAssocRule;
use Dwarf\MeiliTools\Rules\ArrayAssoc;
use Illuminate\Support\ServiceProvider;

class MeiliToolsServiceProvider extends ServiceProvider
{
    /**
     * Actions to bind.
     */
    public array $bindings = [
        ArrayAssocRule::class         => ArrayAssoc::class,
        CreatesIndex::class           => CreateIndex::class,
        DeletesIndex::class           => DeleteIndex::class,
        DetailsIndex::class           => DetailIndex::class,
        EnsuresIndexExists::class     => EnsureIndexExists::class,
        ListsClasses::class           => ListClasses::class,
        ListsIndexes::class           => ListIndexes::class,
        ResetsIndex::class            => ResetIndex::class,
        SynchronizesIndex::class      => SynchronizeIndex::class,
        ValidatesIndexSettings::class => ValidateIndexSettings::class,
        ViewsIndex::class             => ViewIndex::class,
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
        $this->mergeConfigFrom(__DIR__ . '/../config/meilitools.php', 'meilitools');

        if (class_exists('Laravel\Scout\ScoutServiceProvider')) {
            $this->app->register(MeiliToolsScoutServiceProvider::class);
        }
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                IndexCreate::class,
                IndexDelete::class,
                IndexDetails::class,
                IndexReset::class,
                IndexView::class,
                IndexesList::class,
            ]);

            $this->publishes([__DIR__ . '/../config/meilitools.php' => $this->app['path.config'] . '/meilitools.php']);
        }
    }
}
