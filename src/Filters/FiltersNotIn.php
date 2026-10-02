<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Filters;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;

class FiltersNotIn implements Filter
{
    /**
     * @param  Builder<Model>  $query
     */
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $values = is_array($value) ? $value : [$value];

        $query->whereNotIn($property, $values);
    }
}
