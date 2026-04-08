<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Facades;

use Foxws\ScoutBuilder\ScoutBuilderFactory;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Foxws\ScoutBuilder\ScoutBuilder for(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Model|string $subject, ?\Illuminate\Http\Request $request = null)
 *
 * @see ScoutBuilderFactory
 */
class ScoutBuilder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ScoutBuilderFactory::class;
    }
}
