<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Sorts;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;

interface Sort
{
    /**
     * @param  Builder<Model>  $query
     */
    public function __invoke(Builder $query, bool $descending, string $property): void;
}
