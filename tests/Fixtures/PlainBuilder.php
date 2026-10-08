<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;

/**
 * Scout builder used by a model instead of the package's search builder.
 *
 * @template TModel of Model
 *
 * @extends Builder<TModel>
 */
class PlainBuilder extends Builder
{
    //
}
