<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Sorts;

use Laravel\Scout\Builder;

interface Sort
{
    public function __invoke(Builder $query, bool $descending, string $property): void;
}
