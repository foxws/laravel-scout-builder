<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Filters;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Scout\Builder;

class FiltersScope implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $scope = Str::camel($property);
        $values = array_values(Arr::wrap($value));

        $existing = $query->queryCallback;

        $query->query(function (EloquentBuilder $builder) use ($existing, $scope, $values): void {
            if ($existing !== null) {
                ($existing)($builder);
            }

            $builder->$scope(...$values);
        });
    }
}
