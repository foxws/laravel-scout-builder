<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Filters;

use Foxws\ScoutBuilder\Support\FilterValue;
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

        $values = array_values(array_filter($values, fn (mixed $item): bool => is_scalar($item) || $item === null));

        if ($values === []) {
            return;
        }

        foreach ($values as $item) {
            FilterValue::ensureSafe($query, $item);
        }

        // Scout keeps one "where not in" per field, so add to the one that's already there.
        $values = [...array_values($query->whereNotIns[$property] ?? []), ...$values];

        $query->whereNotIn($property, $values);
    }
}
