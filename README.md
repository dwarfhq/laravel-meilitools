# Laravel MeiliTools

[![PHP](https://img.shields.io/packagist/php-v/dwarfdk/laravel-meilitools.svg?style=flat-square)](https://packagist.org/packages/dwarfdk/laravel-meilitools)
[![Packagist](https://img.shields.io/packagist/v/dwarfdk/laravel-meilitools.svg?style=flat-square)](https://packagist.org/packages/dwarfdk/laravel-meilitools)
[![Downloads](https://img.shields.io/packagist/dt/dwarfdk/laravel-meilitools.svg?style=flat-square)](https://packagist.org/packages/dwarfdk/laravel-meilitools)
[![License](https://img.shields.io/github/license/dwarfhq/laravel-meilitools.svg?style=flat-square)](LICENSE)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/dwarfhq/laravel-meilitools/Tests)](https://github.com/dwarfhq/laravel-meilitools/actions)

The purpose of this package is to ease the configuration of indexes for MeiliSearch, so it's possible to use advanced filtering and sorting through Laravel Scout, without having to meddle with their API manually.

## Table of Contents
- [Support](#support)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Model Settings](#model-settings)
    - [Commands](#commands)
- [Testing](#testing)
- [License](#license)

## Support
| Engine  | 0.1.x | 0.2.x | 0.3.x |
|---------|-------|-------|-------|
| v0.26.x |   X   |   X   |       |
| v0.27.x |   X   |   X   |       |
| v0.28.x |       |       |   X   |
| v0.29.x |       |       |   X   |

## Installation
Install this package via Composer:
```bash
$ composer require dwarfdk/laravel-meilitools
```

## Configuration
Publish config using Artisan command:
```bash
$ php artisan vendor:publish --provider="Dwarf\MeiliTools\MeiliToolsServiceProvider"
```
Change configuration through `config/meilitools.php`.

## Usage
This package provides commands and helpers to ease the use of configuring MeiliSearch indexes.

### Model Settings
Setup index settings for a model by implementing the method provided by the contract.
```php
use Dwarf\MeiliTools\Contracts\Indexes\MeiliSettings;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Article extends Model implements MeiliSettings
{
    use Searchable;
    
    /**
     * {@inheritdoc}
     */
    public function meiliSettings(): array
    {
        return ['filterableAttributes' => ['status']];
    }
}
```
A full list of available index settings can be found [here](https://docs.meilisearch.com/learn/configuration/settings.html).

### Commands
The following commands are available:
#### `meili:index:details` - Get details for a MeiliSearch index
**Arguments:**
- `index` : Index name

#### `meili:index:reset` - Reset settings for a MeiliSearch index
**Arguments:**
- `index` : Index name

**Options:**
- `--pretend` : Only shows what changes would have been done to the index

#### `meili:indexes:list` - List all MeiliSearch indexes

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

#### `meili:models:synchronize` - Synchronize all models implementing MeiliSearch index settings
**Options:**
- `--pretend` : Only shows what changes would have been done to the indexes
- `--force` : Force the operation to run when in production

## Testing
Running tests can be done either through composer, or directly calling the PHPUnit binary.
```bash
$ composer test
```
To run tests with code coverage, please make sure that `phpdbg` exists and is executable.
```bash
$ composer test:coverage
$ open tests/_reports/index.html
```

## Career

Dwarf A/S is a digital agency based in Copenhagen (Denmark) and established January 1st 2000.

We're always looking for new talent, so have a look at our [website](https://dwarf.dk/career/php-developer) for job openings.

## License
The MIT License (MIT). Please see [License File](LICENSE) for more information.
