<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Laravel\Scout\Builder as ScoutSearchBuilder;

class ScoutBuilder
{
    /**
     * @template T of Model
     *
     * @param  ScoutSearchBuilder<T>|T|class-string<T>  $subject
     * @return QueryBuilder<T>
     */
    public function for(ScoutSearchBuilder|Model|string $subject, ?Request $request = null): QueryBuilder
    {
        return QueryBuilder::for($subject, $request);
    }
}
