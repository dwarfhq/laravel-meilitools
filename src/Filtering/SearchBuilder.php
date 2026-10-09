<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering;

use Dwarf\MeiliTools\Contracts\Filtering\SearchBuilder as SearchBuilderContract;
use Dwarf\MeiliTools\Filtering\Concerns\BuildsFilters;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Laravel\Scout\Builder as ScoutBuilder;

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
     * Search parameters set by the builder, which take precedence over Scout's `options()`.
     *
     * @var array<string, mixed>
     */
    protected array $searchParameters = [];

    /**
     * Number of matching documents per facet value from the last search.
     *
     * @var array<string, array<string, int>>
     */
    protected array $facetDistribution = [];

    /**
     * Lowest and highest numeric facet values from the last search.
     *
     * @var array<string, array{min: float|int, max: float|int}>
     */
    protected array $facetStats = [];

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

    public function facets(array $attributes): static
    {
        return $this->withOption('facets', $attributes);
    }

    public function facetDistribution(): array
    {
        return $this->facetDistribution;
    }

    public function facetStats(): array
    {
        return $this->facetStats;
    }

    public function rememberResults(array $results): void
    {
        /** @var array<string, array<string, int>> $distribution */
        $distribution = \is_array($results['facetDistribution'] ?? null) ? $results['facetDistribution'] : [];
        /** @var array<string, array{min: float|int, max: float|int}> $stats */
        $stats = \is_array($results['facetStats'] ?? null) ? $results['facetStats'] : [];

        $this->facetDistribution = $distribution;
        $this->facetStats = $stats;
    }

    public function searchParameters(): array
    {
        return $this->searchParameters;
    }

    /**
     * Set a search parameter.
     *
     * @return $this
     */
    protected function withOption(string $key, mixed $value): static
    {
        $this->searchParameters[$key] = $value;

        return $this;
    }
}
