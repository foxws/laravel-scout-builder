<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Sorts;

use Foxws\ScoutBuilder\Enums\EngineFeature;
use Foxws\ScoutBuilder\Support\EngineAwareness;
use Laravel\Scout\Builder;

class SortsOldest implements Sort
{
    public function __construct(protected ?string $column = null) {}

    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        EngineAwareness::ensureFeatureSupport(EngineFeature::FieldSort);

        $column = $this->column ?? $property;

        if ($descending) {
            $query->latest($column);

            return;
        }

        $query->oldest($column);
    }
}
