<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Includes;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder;

class IncludesCount implements Includable
{
    public function __invoke(Builder $query, string $include): void
    {
        $existing = $query->queryCallback;

        $query->query(function (EloquentBuilder $builder) use ($existing, $include): void {
            if ($existing !== null) {
                ($existing)($builder);
            }

            $builder->withCount($include);
        });
    }
}
