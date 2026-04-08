<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Exceptions;

use Illuminate\Support\Collection;

final class InvalidSortQuery extends InvalidQuery
{
    public function __construct(
        public Collection $unknownSorts,
        public Collection $allowedSorts,
    ) {
        $unknownSorts = $this->unknownSorts->implode(', ');
        $allowedSorts = $this->allowedSorts->implode(', ');

        parent::__construct("Requested sort(s) `{$unknownSorts}` are not allowed. Allowed sort(s) are `{$allowedSorts}`.");
    }

    public static function sortsNotAllowed(Collection $unknownSorts, Collection $allowedSorts): static
    {
        return new self($unknownSorts, $allowedSorts);
    }
}
