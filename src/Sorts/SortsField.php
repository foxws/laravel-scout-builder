<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Sorts;

use Foxws\ScoutBuilder\Enums\EngineFeature;
use Foxws\ScoutBuilder\Enums\ScoutDriver;
use Foxws\ScoutBuilder\Support\EngineAwareness;
use Laravel\Scout\Builder;

class SortsField implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        EngineAwareness::ensureFeatureSupport(EngineFeature::FieldSort, ScoutDriver::cases());

        $query->orderBy($property, $descending ? 'desc' : 'asc');
    }
}
