<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Filters;

use Laravel\Scout\Builder;

class FiltersNotIn implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $values = is_array($value) ? $value : [$value];

        $query->whereNotIn($property, $values);
    }
}
