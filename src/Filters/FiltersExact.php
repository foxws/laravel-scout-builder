<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Filters;

use Foxws\ScoutBuilder\Exceptions\InvalidFilterValue;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;

class FiltersExact implements Filter
{
    /**
     * @param  Builder<Model>  $query
     */
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (is_array($value)) {
            if (array_filter($value, is_array(...)) !== []) {
                return;
            }

            if (count($value) !== 1) {
                throw InvalidFilterValue::multipleValues();
            }

            $value = reset($value);
        }

        $query->where($property, $value);
    }
}
