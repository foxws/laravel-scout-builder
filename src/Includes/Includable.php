<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Includes;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;

interface Includable
{
    /**
     * @param  Builder<Model>  $query
     */
    public function __invoke(Builder $query, string $include): void;
}
