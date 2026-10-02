<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Exceptions;

use Illuminate\Support\Collection;

final class InvalidFilterQuery extends InvalidQuery
{
    /**
     * @param  Collection<int, int|string>  $unknownFilters  Requested names; a filter name from the query string can be an integer.
     * @param  Collection<int, string>  $allowedFilters
     */
    public function __construct(
        public Collection $unknownFilters,
        public Collection $allowedFilters,
    ) {
        $unknownFilters = $this->unknownFilters->implode(', ');
        $allowedFilters = $this->allowedFilters->implode(', ');

        parent::__construct("Requested filter(s) `{$unknownFilters}` are not allowed. Allowed filter(s) are `{$allowedFilters}`.");
    }

    /**
     * @param  Collection<int, int|string>  $unknownFilters  Requested names; a filter name from the query string can be an integer.
     * @param  Collection<int, string>  $allowedFilters
     */
    public static function filtersNotAllowed(Collection $unknownFilters, Collection $allowedFilters): static
    {
        return new self($unknownFilters, $allowedFilters);
    }
}
