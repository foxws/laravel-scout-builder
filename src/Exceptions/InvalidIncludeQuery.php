<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Exceptions;

use Illuminate\Support\Collection;

final class InvalidIncludeQuery extends InvalidQuery
{
    /**
     * @param  Collection<int, string>  $unknownIncludes
     * @param  Collection<int, string>  $allowedIncludes
     */
    public function __construct(
        public Collection $unknownIncludes,
        public Collection $allowedIncludes,
    ) {
        $unknownIncludes = $this->unknownIncludes->implode(', ');
        $allowedIncludes = $this->allowedIncludes->implode(', ');

        parent::__construct("Requested include(s) `{$unknownIncludes}` are not allowed. Allowed include(s) are `{$allowedIncludes}`.");
    }

    /**
     * @param  Collection<int, string>  $unknownIncludes
     * @param  Collection<int, string>  $allowedIncludes
     */
    public static function includesNotAllowed(Collection $unknownIncludes, Collection $allowedIncludes): static
    {
        return new self($unknownIncludes, $allowedIncludes);
    }
}
