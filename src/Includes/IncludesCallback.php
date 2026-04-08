<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Includes;

use Laravel\Scout\Builder;

class IncludesCallback implements Includable
{
    public function __construct(protected mixed $callback) {}

    public function __invoke(Builder $query, string $include): void
    {
        ($this->callback)($query, $include);
    }
}
