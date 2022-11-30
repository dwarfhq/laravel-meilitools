<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools;

use Closure;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Searchable as BaseSearchable;
use MeiliSearch\Endpoints\Indexes;

trait Searchable
{
    use BaseSearchable { search as baseSearch; }

    /**
     * Perform a search against the model's indexed data.
     *
     * @param string|null   $query
     * @param \Closure|null $callback
     *
     * @return \Dwarf\MeiliTools\Builder|\Laravel\Scout\Builder
     */
    public static function search(?string $query = '', ?Closure $callback = null)
    {
        // Override search handling when using MeiliSearch.
        if (app(EngineManager::class)->getDefaultDriver() === 'meilisearch') {
            $builder = app(Builder::class, [
                'model'    => new static(),
                'query'    => $query,
                'callback' => function (Indexes $index, ?string $query, array $params) use ($builder, $callback) {
                    $filter =  $builder->filter();
                    if (!empty($filter)) {
                        $params['filter'] = empty($params['filter']) ? $filter : "{$params['filter']} AND ({$filter})";
                    }

                    if ($callback) {
                        return $callback($index, $query, $params);
                    }

                    return $index->rawSearch($query, $params);
                },
                'softDelete' => static::usesSoftDelete() && config('scout.soft_delete', false),
            ]);

            return $builder;
        }

        return static::baseSearch($query, $callback);
    }
}
