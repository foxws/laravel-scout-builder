<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Filters;

use Laravel\Scout\Builder;

class FiltersExact implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $query->where($property, $value);
    }
}
