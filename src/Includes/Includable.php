<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Includes;

use Laravel\Scout\Builder;

interface Includable
{
    public function __invoke(Builder $query, string $include): void;
}
