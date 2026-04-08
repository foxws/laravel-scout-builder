<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Foxws\ScoutBuilder\QueryBuilder for(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Model|string $subject, ?\Illuminate\Http\Request $request = null)
 *
 * @see \Foxws\ScoutBuilder\ScoutBuilder
 */
class ScoutBuilder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Foxws\ScoutBuilder\ScoutBuilder::class;
    }
}
