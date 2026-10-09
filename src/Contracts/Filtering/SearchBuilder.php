<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Contracts\Filtering;

use InvalidArgumentException;

/**
 * Scout search builder with MeiliSearch filtering and search options.
 *
 * Implementations must extend Scout's builder, as they're returned when searching models.
 */
interface SearchBuilder extends FilterBuilder
{
    /**
     * Sort by distance to a geo point.
     *
     * @return $this
     */
    public function orderByGeo(float $lat, float $lng, string $direction = 'asc'): static;

    /**
     * Set the strategy for matching query terms: `last`, `all` or `frequency`.
     *
     * @throws InvalidArgumentException When the strategy is invalid.
     *
     * @return $this
     */
    public function matchingStrategy(string $strategy): static;

    /**
     * Exclude results with a ranking score below the threshold, between 0 and 1.
     *
     * @throws InvalidArgumentException When the threshold is out of range.
     *
     * @return $this
     */
    public function rankingScoreThreshold(float $threshold): static;

    /**
     * Restrict the search to the given searchable attributes.
     *
     * @param list<string> $attributes
     *
     * @return $this
     */
    public function attributesToSearchOn(array $attributes): static;

    /**
     * Return at most one result per value of the given filterable attribute.
     *
     * @return $this
     */
    public function distinct(string $attribute): static;

    /**
     * Search using the given locales, e.g. `jpn` or `eng`.
     *
     * @param list<string> $locales
     *
     * @return $this
     */
    public function locales(array $locales): static;

    /**
     * Get the search parameters set by the builder, e.g. the matching strategy.
     *
     * @return array<string, mixed>
     */
    public function searchParameters(): array;

    /**
     * Count the matching documents per value of the given filterable attributes when searching.
     *
     * @param list<string> $attributes
     *
     * @return $this
     */
    public function facets(array $attributes): static;

    /**
     * Get the number of matching documents per facet value, available after searching.
     *
     * @return array<string, array<string, int>>
     */
    public function facetDistribution(): array;

    /**
     * Get the lowest and highest numeric facet values, available after searching.
     *
     * @return array<string, array{min: float|int, max: float|int}>
     */
    public function facetStats(): array;

    /**
     * Remember the facets of raw search results, which the engine calls after searching.
     *
     * @param array<array-key, mixed> $results
     */
    public function rememberResults(array $results): void;
}
