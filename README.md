# Laravel MeiliTools

[![PHP](https://img.shields.io/packagist/php-v/dwarfdk/laravel-meilitools.svg?style=flat-square)](https://packagist.org/packages/dwarfdk/laravel-meilitools)
[![Packagist](https://img.shields.io/packagist/v/dwarfdk/laravel-meilitools.svg?style=flat-square)](https://packagist.org/packages/dwarfdk/laravel-meilitools)
[![Downloads](https://img.shields.io/packagist/dt/dwarfdk/laravel-meilitools.svg?style=flat-square)](https://packagist.org/packages/dwarfdk/laravel-meilitools)
[![License](https://img.shields.io/github/license/dwarfhq/laravel-meilitools.svg?style=flat-square)](LICENSE)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/dwarfhq/laravel-meilitools/tests.yml?branch=main)](https://github.com/dwarfhq/laravel-meilitools/actions)

The purpose of this package is to ease the configuration of indexes for MeiliSearch, so it's possible to use advanced filtering and sorting through Laravel Scout, without having to meddle with their API manually.

## Table of Contents
- [Compatibility](#compatibility)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Index Settings](#index-settings)
    - [Filtering](#filtering)
    - [Commands](#commands)
- [Examples](#examples)
- [Upgrading](#upgrading)
- [Development](#development)
- [License](#license)

## Compatibility
| Package | PHP        | Laravel     | Scout       | MeiliSearch engine |
|---------|------------|-------------|-------------|--------------------|
| 1.x     | 8.4 - 8.5  | 12.x - 13.x | 10.x - 11.x | >= v1.36.0         |
| 0.4.x   | 8.2 - 8.4  | 10.x - 12.x | 9.x - 10.x  | v1.x.x             |
| 0.3.x   |            |             |             | v0.28.x - v0.30.x  |
| 0.2.x   |            |             |             | v0.26.x - v0.27.x  |
| 0.1.x   |            |             |             | v0.26.x - v0.27.x  |

MeiliSearch v1.36.0 changed the default ranking rules, which is the latest engine change requiring changes in this package.

## Installation
Install this package via Composer:
```bash
composer require dwarfdk/laravel-meilitools
```

## Configuration
Publish config using Artisan command:
```bash
php artisan vendor:publish --provider="Dwarf\MeiliTools\MeiliToolsServiceProvider"
```
Change configuration through `config/meilitools.php`, which contains the paths scanned for models when synchronizing all models.

## Usage
This package provides commands and helpers to ease the use of configuring MeiliSearch indexes.

All settings are validated before being sent to MeiliSearch, and only actual changes are applied.
The following settings are supported:
`dictionary`, `displayedAttributes`, `distinctAttribute`, `facetSearch`, `faceting`, `filterableAttributes`,
`localizedAttributes`, `nonSeparatorTokens`, `pagination`, `prefixSearch`, `proximityPrecision`, `rankingRules`,
`searchCutoffMs`, `searchableAttributes`, `separatorTokens`, `sortableAttributes`, `stopWords`, `synonyms` and `typoTolerance`.

Experimental settings and `embedders` are not managed, and are ignored if present.
A full description of the index settings can be found [here](https://www.meilisearch.com/docs/reference/api/settings).

### Index Settings
Index settings can be defined in Scout's configuration, on the model, or both.

#### Scout Configuration
Settings defined in Scout's `meilisearch.index-settings` configuration are used by this package as well,
keyed by either model class or index name, the same way as Scout's `scout:sync-index-settings` command.
```php
// config/scout.php
'meilisearch' => [
    'host'           => env('MEILISEARCH_HOST', 'http://localhost:7700'),
    'key'            => env('MEILISEARCH_KEY'),
    'index-settings' => [
        Article::class => [
            'filterableAttributes' => ['status'],
            'sortableAttributes'   => ['published_at'],
        ],
        'books' => [
            'searchableAttributes' => ['title', 'author'],
        ],
    ],
],
```
Index names are prefixed with Scout's `prefix` configuration, unless already prefixed.

#### Model Settings
Setup index settings for a model by implementing the method provided by the contract.
```php
use Dwarf\MeiliTools\Contracts\Indexes\MeiliSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Article extends Model implements MeiliSettings
{
    use Searchable;
    use SoftDeletes;

    /**
     * {@inheritdoc}
     */
    public function meiliSettings(): array
    {
        // When using soft deletes '__soft_deleted' will automatically be added to filterable attributes.
        return ['filterableAttributes' => ['status']];
    }
}
```
Settings for a model are merged from Scout's configuration keyed by the model's index name, Scout's configuration
keyed by the model class, and the model's `meiliSettings()` method, with later sources taking precedence.

### Filtering
Searching models using MeiliSearch returns a builder with an Eloquent style API for MeiliSearch filters,
in addition to Scout's own `where`, `whereIn` and `whereNotIn` methods.
Filtered attributes must be filterable, and sorted attributes must be sortable, in the index settings.
```php
use Dwarf\MeiliTools\Contracts\Filtering\FilterBuilder;

$articles = Article::search('laravel')
    ->where('status', 'published')
    ->where('views', '>=', 100)
    ->where(fn (FilterBuilder $filter) => $filter
        ->whereIn('category', ['news', 'tutorials'])
        ->orWhereNull('category'))
    ->whereNot('author', 'bot')
    ->get();
```
Scout declares `search()` as returning its own builder, so for IDE and static analysis support, add the following to the model:
```php
/**
 * @method static \Dwarf\MeiliTools\Filtering\SearchBuilder<static> search(string $query = '', ?\Closure $callback = null)
 */
class Article extends Model
```

Values are formatted for MeiliSearch: strings are quoted and escaped, backed enums use their value,
and dates are converted to Unix timestamps, so dates must be indexed as timestamps to be filterable.

The following filter methods are available, each with `orWhere` variants, and most with `whereNot` variants:

| Method | MeiliSearch filter |
|--------|--------------------|
| `where('rank', '>', 3)`, `where('rank', 3)` | `rank > 3`, `rank = 3` (operators `=`, `!=`, `>`, `>=`, `<`, `<=`) |
| `where(fn (FilterBuilder $filter) => ...)` | Nested group in parentheses |
| `whereNot('genre', 'drama')` | `NOT (genre = "drama")`, also accepting a closure |
| `whereIn('genre', [...])`, `whereNotIn(...)` | `genre IN [...]`, `genre NOT IN [...]` |
| `whereBetween('rank', [1, 5])` | `rank 1 TO 5` |
| `whereNull('rank')`, `where('rank', null)` | `rank IS NULL` |
| `whereEmpty('tags')` | `tags IS EMPTY` |
| `whereExists('rank')` | `rank EXISTS` |
| `whereStartsWith('title', 'Bat')` | `title STARTS WITH "Bat"` |
| `whereContains('title', 'man')` | `title CONTAINS "man"`, requiring the experimental `containsFilter` feature |
| `whereGeoRadius($lat, $lng, $distance, DistanceUnit::Meters)` | `_geoRadius(lat, lng, meters)`, with the distance converted to meters from the `Enums\Filtering\DistanceUnit` cases `Meters`, `Kilometers`, `Miles` or `Feet` |
| `whereGeoBoundingBox([$lat, $lng], [$lat, $lng])` | `_geoBoundingBox([lat, lng], [lat, lng])`, with the top right and bottom left corners |
| `whereGeoPolygon([[$lat, $lng], ...])` | `_geoPolygon([lat, lng], ...)`, requiring `_geojson` to be filterable |
| `whereRaw('rank = 3 OR genre = drama')` | Raw filter expression in parentheses |

The builder also adds the following search options:
```php
Article::search('laravel')
    ->orderByGeo($lat, $lng)           // Sort by distance, using `_geoPoint(lat, lng):asc`
    ->matchingStrategy('frequency')    // `last`, `all` or `frequency`
    ->rankingScoreThreshold(0.5)       // Exclude results ranked below the threshold
    ->attributesToSearchOn(['title'])  // Restrict which searchable attributes are searched
    ->distinct('slug')                 // Return one result per value of a filterable attribute
    ->locales(['eng'])                 // Search using specific locales
    ->get();
```
These options take precedence over the same keys given to Scout's `options()`.
The filter is combined with any filter Scout sets, e.g. for soft deletes, and with a `filter` given to `options()`,
and the search parameters are also given to a search callback.

Filters and search parameters are applied by the package's MeiliSearch engine, which replaces Scout's `meilisearch` engine.
If you register your own MeiliSearch engine, use the `Dwarf\MeiliTools\Filtering\Concerns\AppliesSearchBuilder` trait in it.

Filtering behaviour can be changed by binding your own implementations of the contracts in the service container:

| Contract | Default | Purpose |
|----------|---------|---------|
| `Contracts\Filtering\SearchBuilder` | `Filtering\SearchBuilder` | Builder returned by `Model::search()`, which must extend Scout's builder and not be a singleton |
| `Contracts\Filtering\FilterBuilder` | `Filtering\FilterBuilder` | Builder given to nested filter closures |
| `Contracts\Filtering\FormatsFilterValues` | `Filtering\FilterValueFormatter` | Formatting of fields and values, e.g. dates |

For example, to filter on dates indexed as `Y-m-d` strings instead of timestamps:
```php
use Dwarf\MeiliTools\Contracts\Filtering\FormatsFilterValues;
use Dwarf\MeiliTools\Filtering\FilterValueFormatter;

$this->app->bind(FormatsFilterValues::class, fn () => new class extends FilterValueFormatter {
    public function value(mixed $value): string
    {
        return parent::value($value instanceof DateTimeInterface ? $value->format('Y-m-d') : $value);
    }
});
```

### Commands
The following commands are available:

#### `meili:index:create` - Create a MeiliSearch index
**Arguments:**
- `index` : Index name

#### `meili:index:delete` - Delete a MeiliSearch index
**Arguments:**
- `index` : Index name

**Options:**
- `--force` : Force the operation to run

#### `meili:index:details` - Get details for a MeiliSearch index
**Arguments:**
- `index` : Index name

#### `meili:index:reset` - Reset settings for a MeiliSearch index
**Arguments:**
- `index` : Index name

**Options:**
- `--pretend` : Only shows what changes would have been done to the index

#### `meili:index:synchronize` - Synchronize settings for a MeiliSearch index configured in Scout
**Arguments:**
- `index` : Index name, as configured in Scout's `meilisearch.index-settings`

**Options:**
- `--pretend` : Only shows what changes would have been done to the index
- `--check` : Only checks whether the settings are in sync, failing when they are not

#### `meili:index:view` - Get base information about a MeiliSearch index
**Arguments:**
- `index` : Index name

**Options:**
- `--stats` : Whether to include index stats

#### `meili:indexes:list` - List all MeiliSearch indexes
**Options:**
- `--stats` : Whether to include index stats

#### `meili:indexes:synchronize` - Synchronize all MeiliSearch indexes configured in Scout which are not bound to a model
Indexes belonging to models synchronized by `meili:models:synchronize` are skipped. Exits with a failure code if any index fails to synchronize.

**Options:**
- `--pretend` : Only shows what changes would have been done to the indexes
- `--check` : Only checks whether the settings are in sync, failing when they are not
- `--force` : Force the operation to run when in production

#### `meili:model:details` - Get details for a MeiliSearch model index
**Arguments:**
- `model` : Model class

#### `meili:model:reset` - Reset settings for a MeiliSearch model index
**Arguments:**
- `model` : Model class

**Options:**
- `--pretend` : Only shows what changes would have been done to the index

#### `meili:model:synchronize` - Synchronize settings for a MeiliSearch model index
**Arguments:**
- `model` : Model class

**Options:**
- `--pretend` : Only shows what changes would have been done to the index
- `--check` : Only checks whether the settings are in sync, failing when they are not

#### `meili:model:view` - Get base information about a MeiliSearch model index
**Arguments:**
- `model` : Model class

**Options:**
- `--stats` : Whether to include index stats

#### `meili:models:synchronize` - Synchronize all models with MeiliSearch index settings
Synchronizes models in the configured paths implementing `MeiliSettings`, as well as models configured in Scout's `meilisearch.index-settings`.
Exits with a failure code if any model fails to synchronize.

**Options:**
- `--pretend` : Only shows what changes would have been done to the indexes
- `--check` : Only checks whether the settings are in sync, failing when they are not
- `--force` : Force the operation to run when in production

#### `meili:stats` - Get the stats of MeiliSearch or a MeiliSearch index
Shows the database size and the documents of each index, or the stats of a single index, including how many documents contain each field.

**Arguments:**
- `index` : Index name, showing the stats of all indexes when omitted

#### `meili:tasks` - List the most recent MeiliSearch tasks
Shows the status, duration and any error of each task, e.g. to find out why documents weren't indexed.

**Options:**
- `--status=*` : Only list tasks with the status, e.g. `failed`
- `--type=*` : Only list tasks of the type, e.g. `documentAdditionOrUpdate`
- `--index=*` : Only list tasks of the index
- `--limit=20` : Maximum number of tasks to list

#### `meili:tasks:cancel` - Cancel enqueued and processing MeiliSearch tasks
At least one filter is required.

**Options:**
- `--status=*` : Only cancel tasks with the status, `enqueued` or `processing`
- `--type=*` : Only cancel tasks of the type, e.g. `documentAdditionOrUpdate`
- `--index=*` : Only cancel tasks of the index
- `--uid=*` : Only cancel the task with the uid
- `--force` : Force the operation to run

## Examples
The `--check` option of the synchronize commands fails when settings are out of sync without changing them,
e.g. to verify settings in CI or before deploying:
```
$ php artisan meili:models:synchronize --check
$ php artisan meili:indexes:synchronize --check
```

Model commands can take both full class name and base name, with the latter being completed using the configured paths.
```
$ php artisan meili:model:details App\\Models\\Article
$ php artisan meili:model:details Article

$ php artisan meili:model:reset App\\Models\\Article
$ php artisan meili:model:reset Article

$ php artisan meili:model:synchronize App\\Models\\Article
$ php artisan meili:model:synchronize Article

$ php artisan meili:model:view App\\Models\\Article
$ php artisan meili:model:view Article
```

Recent failed tasks, e.g. documents which couldn't be indexed, can be listed with:
```
$ php artisan meili:tasks --status=failed
```

## Upgrading
### From 0.4.x to 1.0.0
- PHP 8.4, Laravel 12, Scout 10 and MeiliSearch v1.36.0 are now the minimum supported versions.
- `laravel/scout` is now a required dependency, and `MeiliToolsScoutServiceProvider` has been merged into `MeiliToolsServiceProvider`.
- Engine version checks were removed, so `Helpers::defaultSettings()` and `ValidatesIndexSettings` methods no longer accept a version argument.
- Actions use the `Meilisearch\Client` instead of Scout's `EngineManager`, and action contracts now include all parameters.
- List settings are validated as lists, and booleans and integers are validated strictly, e.g. `'1'` is no longer accepted as `true`.
- `ArrayAssocRule` now extends `ValidationRule` instead of the deprecated `Rule` contract.
- Models without `MeiliSettings` can be synchronized using settings from Scout's configuration, instead of throwing an exception.
- Searching models using MeiliSearch returns the package's search builder, and Scout's `meilisearch` engine is replaced to apply it.
  Scout's `where` now only accepts the `=`, `!=`, `<>`, `>`, `>=`, `<` and `<=` operators, throwing on others,
  and `null` values are left out of `whereIn` and `whereNotIn`, as they never matched.

## Development
Tests run against a MeiliSearch instance at `http://localhost:7700` with the master key `MeiliToolsMasterKey`, as configured in `phpunit.xml`.
```bash
meilisearch --master-key MeiliToolsMasterKey
```
Run all checks (Composer Normalize, Rector, Pint, PHPStan and type coverage) followed by the tests, or with a security audit as done in CI:
```bash
composer test
composer ci:check
```
Run tests with code coverage:
```bash
composer test:coverage
```
Fix code style and apply Rector refactorings:
```bash
composer lint
composer rector:fix
```
[Laravel Boost](https://github.com/laravel/boost) is included as a development dependency, and runs through Testbench.
Its files (`AGENTS.md`, `boost.json`, `.mcp.json` etc.) are git-ignored, so set it up locally:
```bash
vendor/bin/testbench boost:install
cp vendor/orchestra/testbench-core/laravel/boost.json boost.json
```
Since packages don't have an `artisan` file, change the generated MCP server command to `php vendor/bin/testbench boost:mcp`.
Guidelines, including the project guidelines in `.ai/guidelines`, can afterwards be updated with `composer boost:update`.

## Career

Dwarf A/S is a digital agency based in Copenhagen (Denmark) and established January 1st 2000.

We're always looking for new talent, so have a look at our [website](https://dwarf.dk/career/php-developer) for job openings.

## License
The MIT License (MIT). Please see [License File](LICENSE) for more information.
