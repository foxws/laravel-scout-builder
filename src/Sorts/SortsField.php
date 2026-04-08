<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Sorts;

use Laravel\Scout\Builder;

class SortsField implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        $query->orderBy($property, $descending ? 'desc' : 'asc');
    }
}
