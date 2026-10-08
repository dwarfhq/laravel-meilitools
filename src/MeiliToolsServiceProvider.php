<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools;

use Dwarf\MeiliTools\Actions\CreateIndex;
use Dwarf\MeiliTools\Actions\DeleteIndex;
use Dwarf\MeiliTools\Actions\DetailIndex;
use Dwarf\MeiliTools\Actions\DetailModel;
use Dwarf\MeiliTools\Actions\EnsureIndexExists;
use Dwarf\MeiliTools\Actions\ListClasses;
use Dwarf\MeiliTools\Actions\ListIndexes;
use Dwarf\MeiliTools\Actions\ListModels;
use Dwarf\MeiliTools\Actions\ResetIndex;
use Dwarf\MeiliTools\Actions\ResetModel;
use Dwarf\MeiliTools\Actions\ResolveModelSettings;
use Dwarf\MeiliTools\Actions\SynchronizeIndex;
use Dwarf\MeiliTools\Actions\SynchronizeModel;
use Dwarf\MeiliTools\Actions\SynchronizeModels;
use Dwarf\MeiliTools\Actions\SynchronizeScoutIndex;
use Dwarf\MeiliTools\Actions\SynchronizeScoutIndexes;
use Dwarf\MeiliTools\Actions\ValidateIndexSettings;
use Dwarf\MeiliTools\Actions\ViewIndex;
use Dwarf\MeiliTools\Actions\ViewModel;
use Dwarf\MeiliTools\Console\Commands\IndexCreate;
use Dwarf\MeiliTools\Console\Commands\IndexDelete;
use Dwarf\MeiliTools\Console\Commands\IndexDetails;
use Dwarf\MeiliTools\Console\Commands\IndexesList;
use Dwarf\MeiliTools\Console\Commands\IndexesSynchronize;
use Dwarf\MeiliTools\Console\Commands\IndexReset;
use Dwarf\MeiliTools\Console\Commands\IndexSynchronize;
use Dwarf\MeiliTools\Console\Commands\IndexView;
use Dwarf\MeiliTools\Console\Commands\ModelDetails;
use Dwarf\MeiliTools\Console\Commands\ModelReset;
use Dwarf\MeiliTools\Console\Commands\ModelsSynchronize;
use Dwarf\MeiliTools\Console\Commands\ModelSynchronize;
use Dwarf\MeiliTools\Console\Commands\ModelView;
use Dwarf\MeiliTools\Contracts\Actions\CreatesIndex;
use Dwarf\MeiliTools\Contracts\Actions\DeletesIndex;
use Dwarf\MeiliTools\Contracts\Actions\DetailsIndex;
use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Contracts\Actions\ListsClasses;
use Dwarf\MeiliTools\Contracts\Actions\ListsIndexes;
use Dwarf\MeiliTools\Contracts\Actions\ListsModels;
use Dwarf\MeiliTools\Contracts\Actions\ResetsIndex;
use Dwarf\MeiliTools\Contracts\Actions\ResetsModel;
use Dwarf\MeiliTools\Contracts\Actions\ResolvesModelSettings;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModels;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesScoutIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesScoutIndexes;
use Dwarf\MeiliTools\Contracts\Actions\ValidatesIndexSettings;
use Dwarf\MeiliTools\Contracts\Actions\ViewsIndex;
use Dwarf\MeiliTools\Contracts\Actions\ViewsModel;
use Dwarf\MeiliTools\Contracts\Filtering\FilterBuilder as FilterBuilderContract;
use Dwarf\MeiliTools\Contracts\Filtering\FormatsFilterValues;
use Dwarf\MeiliTools\Contracts\Filtering\SearchBuilder as SearchBuilderContract;
use Dwarf\MeiliTools\Contracts\Rules\ArrayAssocRule;
use Dwarf\MeiliTools\Exceptions\MeiliToolsException;
use Dwarf\MeiliTools\Filtering\FilterBuilder;
use Dwarf\MeiliTools\Filtering\FilterValueFormatter;
use Dwarf\MeiliTools\Filtering\MeilisearchEngine;
use Dwarf\MeiliTools\Filtering\SearchBuilder;
use Dwarf\MeiliTools\Rules\ArrayAssoc;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\Builder as ScoutBuilder;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\MeilisearchEngine as ScoutMeilisearchEngine;
use Meilisearch\Client;

class MeiliToolsServiceProvider extends ServiceProvider
{
    /**
     * Actions to bind.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        ArrayAssocRule::class           => ArrayAssoc::class,
        CreatesIndex::class             => CreateIndex::class,
        DeletesIndex::class             => DeleteIndex::class,
        DetailsIndex::class             => DetailIndex::class,
        DetailsModel::class             => DetailModel::class,
        EnsuresIndexExists::class       => EnsureIndexExists::class,
        FilterBuilderContract::class    => FilterBuilder::class,
        FormatsFilterValues::class      => FilterValueFormatter::class,
        ListsClasses::class             => ListClasses::class,
        ListsIndexes::class             => ListIndexes::class,
        ListsModels::class              => ListModels::class,
        ResetsIndex::class              => ResetIndex::class,
        ResetsModel::class              => ResetModel::class,
        ResolvesModelSettings::class    => ResolveModelSettings::class,
        SearchBuilderContract::class    => SearchBuilder::class,
        SynchronizesIndex::class        => SynchronizeIndex::class,
        SynchronizesModel::class        => SynchronizeModel::class,
        SynchronizesModels::class       => SynchronizeModels::class,
        SynchronizesScoutIndex::class   => SynchronizeScoutIndex::class,
        SynchronizesScoutIndexes::class => SynchronizeScoutIndexes::class,
        ValidatesIndexSettings::class   => ValidateIndexSettings::class,
        ViewsIndex::class               => ViewIndex::class,
        ViewsModel::class               => ViewModel::class,
    ];

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/meilitools.php', 'meilitools');

        // The engine applies the search builder's filters and parameters when searching.
        $this->callAfterResolving(EngineManager::class, function (EngineManager $manager): void {
            // Built through the container, as only Scout 11 accepts the MeiliSearch configuration.
            $manager->extend('meilisearch', function (Application $app): MeilisearchEngine {
                return $app->make(MeilisearchEngine::class, [
                    'meilisearch' => $app->make(Client::class),
                    'softDelete'  => config('scout.soft_delete', false),
                    'config'      => config('scout.meilisearch', []),
                ]);
            });
        });

        // Scout resolves its search builder through the container, so models using MeiliSearch get the search builder.
        $this->app->bind(function (Application $app, array $parameters): ScoutBuilder {
            $model = $parameters['model'] ?? null;
            $usesMeiliSearch = $model instanceof Model
                && method_exists($model, 'searchableUsing')
                && $model->searchableUsing() instanceof ScoutMeilisearchEngine;

            if (!$usesMeiliSearch) {
                return new ScoutBuilder(...$parameters);
            }

            if ($app->isShared(SearchBuilderContract::class)) {
                throw new MeiliToolsException('The search builder must not be bound as a singleton');
            }

            $builder = $app->make(SearchBuilderContract::class, $parameters);
            if (!$builder instanceof ScoutBuilder) {
                throw new MeiliToolsException(
                    \sprintf("The search builder [%s] must extend Scout's builder", $builder::class),
                );
            }

            return $builder;
        });
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
                IndexSynchronize::class,
                IndexView::class,
                IndexesList::class,
                IndexesSynchronize::class,
                ModelDetails::class,
                ModelReset::class,
                ModelSynchronize::class,
                ModelView::class,
                ModelsSynchronize::class,
            ]);

            $this->publishes([__DIR__ . '/../config/meilitools.php' => $this->app->configPath('meilitools.php')]);
        }
    }
}
