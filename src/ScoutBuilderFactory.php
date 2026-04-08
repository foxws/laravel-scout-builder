<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Laravel\Scout\Builder;

class ScoutBuilderFactory
{
    /**
     * @template T of Model
     *
     * @param  Builder<T>|T|class-string<T>  $subject
     * @return ScoutBuilder<T>
     */
    public function for(Builder|Model|string $subject, ?Request $request = null): ScoutBuilder
    {
        return ScoutBuilder::for($subject, $request);
    }
}
