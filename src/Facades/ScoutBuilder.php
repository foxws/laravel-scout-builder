<?php

namespace Foxws\ScoutBuilder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Foxws\ScoutBuilder\ScoutBuilder
 */
class ScoutBuilder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Foxws\ScoutBuilder\ScoutBuilder::class;
    }
}
