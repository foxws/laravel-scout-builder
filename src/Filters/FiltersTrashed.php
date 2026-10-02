<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Filters;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;

class FiltersTrashed implements Filter
{
    /**
     * @param  Builder<Model>  $query
     */
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if ($value === 'only') {
            $query->onlyTrashed();

            return;
        }

        if ($value === true || $value === 1 || $value === '1' || $value === 'with') {
            $query->withTrashed();
        }
    }
}
