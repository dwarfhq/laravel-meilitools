<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering;

use Closure;
use Dwarf\MeiliTools\Contracts\Filtering\SearchBuilder as SearchBuilderContract;
use Dwarf\MeiliTools\Filtering\Concerns\BuildsFilters;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Laravel\Scout\Builder as ScoutBuilder;
use Meilisearch\Endpoints\Indexes;

/**
 * Scout builder with advanced MeiliSearch filtering and search options.
 *
 * @template TModel of Model
 *
 * @extends ScoutBuilder<TModel>
 */
class SearchBuilder extends ScoutBuilder implements SearchBuilderContract
{
    use BuildsFilters;

    /**
     * {@inheritDoc}
     *
     * @param TModel        $model
     * @param string        $query
     * @param callable|null $callback
     * @param bool          $softDelete
     */
    public function __construct($model, $query, $callback = null, $softDelete = false)
    {
        parent::__construct($model, $query, $this->filterCallback($callback), $softDelete);
    }

    public function orderByGeo(float $lat, float $lng, string $direction = 'asc'): static
    {
        return $this->orderBy(
            \sprintf('_geoPoint(%s, %s)', $this->formatter()->number($lat), $this->formatter()->number($lng)),
            $direction,
        );
    }

    public function matchingStrategy(string $strategy): static
    {
        if (!\in_array($strategy, ['last', 'all', 'frequency'], true)) {
            throw new InvalidArgumentException(\sprintf('Invalid matching strategy [%s]', $strategy));
        }

        return $this->withOption('matchingStrategy', $strategy);
    }

    public function rankingScoreThreshold(float $threshold): static
    {
        if ($threshold < 0 || $threshold > 1) {
            throw new InvalidArgumentException('The ranking score threshold must be between 0 and 1');
        }

        return $this->withOption('rankingScoreThreshold', $threshold);
    }

    public function attributesToSearchOn(array $attributes): static
    {
        return $this->withOption('attributesToSearchOn', $attributes);
    }

    public function distinct(string $attribute): static
    {
        return $this->withOption('distinct', $attribute);
    }

    public function locales(array $locales): static
    {
        return $this->withOption('locales', $locales);
    }

    /**
     * Merge a search option into the existing options.
     *
     * @return $this
     */
    protected function withOption(string $key, mixed $value): static
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * Wrap the search callback, adding the filter expression to the search parameters.
     */
    protected function filterCallback(?callable $callback): Closure
    {
        return function (Indexes $index, ?string $query, array $options) use ($callback): mixed {
            $filter = $this->toFilter();
            if ($filter !== '') {
                // Scout sets its own filters, e.g. for soft deletes, which must also apply.
                $options['filter'] = match (true) {
                    empty($options['filter'])     => $filter,
                    \is_array($options['filter']) => [...$options['filter'], $filter],
                    default                       => \sprintf('(%s) AND (%s)', $options['filter'], $filter),
                };
            }

            return $callback !== null ? $callback($index, $query, $options) : $index->rawSearch($query, $options);
        };
    }
}
