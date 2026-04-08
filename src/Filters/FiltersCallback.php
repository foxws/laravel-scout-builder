<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Filters;

use Laravel\Scout\Builder;

class FiltersCallback implements Filter
{
    /** @var callable */
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        call_user_func($this->callback, $query, $value, $property);
    }
}
