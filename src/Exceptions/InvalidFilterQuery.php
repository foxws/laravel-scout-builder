<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Exceptions;

use Illuminate\Support\Collection;

class InvalidFilterQuery extends InvalidQuery
{
    public function __construct(
        public Collection $unknownFilters,
        public Collection $allowedFilters,
    ) {
        $unknownFilters = $this->unknownFilters->implode(', ');
        $allowedFilters = $this->allowedFilters->implode(', ');

        parent::__construct("Requested filter(s) `{$unknownFilters}` are not allowed. Allowed filter(s) are `{$allowedFilters}`.");
    }

    public static function filtersNotAllowed(Collection $unknownFilters, Collection $allowedFilters): static
    {
        return new static($unknownFilters, $allowedFilters);
    }
}
