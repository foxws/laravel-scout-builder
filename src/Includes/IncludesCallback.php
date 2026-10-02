<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Includes;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;

class IncludesCallback implements Includable
{
    public function __construct(protected mixed $callback) {}

    /**
     * @param  Builder<Model>  $query
     */
    public function __invoke(Builder $query, string $include): void
    {
        ($this->callback)($query, $include);
    }
}
