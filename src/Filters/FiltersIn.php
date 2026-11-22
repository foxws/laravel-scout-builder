<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Filters;

use BackedEnum;
use Foxws\ScoutBuilder\Support\FilterValue;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;
use Stringable;

class FiltersIn implements Filter
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

        if (array_key_exists($property, $query->whereIns)) {
            $values = $this->valuesWithinConstraint($query->whereIns[$property], $values);
        }

        $query->whereIn($property, $values);
    }

    /**
     * Scout keeps one "where in" per field, so the requested values would replace
     * the constraint that's already there, such as the teams a user may search.
     *
     * @param  array<array-key, mixed>  $constraint
     * @param  list<mixed>  $values
     * @return list<mixed>
     */
    protected function valuesWithinConstraint(array $constraint, array $values): array
    {
        $requested = array_map($this->comparableValue(...), $values);

        return array_values(array_filter(
            $constraint,
            fn (mixed $allowed): bool => in_array($this->comparableValue($allowed), $requested, true),
        ));
    }

    /**
     * Request values are cast, so "2" and 2, and true and 1, are the same value.
     */
    protected function comparableValue(mixed $value): mixed
    {
        return match (true) {
            is_bool($value) => $value ? '1' : '0',
            $value instanceof BackedEnum => (string) $value->value,
            is_scalar($value), $value instanceof Stringable => (string) $value,
            default => $value,
        };
    }
}
