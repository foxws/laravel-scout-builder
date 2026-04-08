<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Sorts;

use Laravel\Scout\Builder;

class SortsCallback implements Sort
{
    /** @var callable */
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        call_user_func($this->callback, $query, $descending, $property);
    }
}
