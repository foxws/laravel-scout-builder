<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Laravel\Scout\Builder as LaravelScoutBuilder;

class ScoutBuilderFactory
{
    /**
     * @template T of Model
     *
     * @param  LaravelScoutBuilder<T>|T|class-string<T>  $subject
     * @return ScoutBuilder<T>
     */
    public function for(LaravelScoutBuilder|Model|string $subject, ?Request $request = null): ScoutBuilder
    {
        return ScoutBuilder::for($subject, $request);
    }
}
