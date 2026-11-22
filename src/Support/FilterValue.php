<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Support;

use Foxws\ScoutBuilder\Exceptions\InvalidFilterValue;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\TypesenseEngine;
use Stringable;

class FilterValue
{
    /**
     * The object and geo filter syntax of Typesense, which is rejected in values.
     */
    protected const string TYPESENSE_SYNTAX = '/\.\{|: *(?:\[ *)?\(/';

    /**
     * @param  Builder<Model>  $query
     */
    public static function ensureSafe(Builder $query, mixed $value): void
    {
        if (! is_string($value) && ! $value instanceof Stringable) {
            return;
        }

        // Check the value as Scout sends it, which is without backticks.
        $quotedValue = str_replace('`', '', (string) $value);

        if (preg_match(static::TYPESENSE_SYNTAX, $quotedValue) !== 1) {
            return;
        }

        $model = $query->model;

        if (method_exists($model, 'searchableUsing') && $model->searchableUsing() instanceof TypesenseEngine) {
            throw InvalidFilterValue::typesenseSyntax();
        }
    }
}
