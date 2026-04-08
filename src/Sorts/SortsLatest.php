<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Sorts;

use Foxws\ScoutBuilder\Support\EngineAwareness;
use Laravel\Scout\Builder;

class SortsLatest implements Sort
{
    public function __construct(protected ?string $column = null) {}

    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        EngineAwareness::ensureFeatureSupport('field_sort', [
            'database',
            'collection',
            'algolia',
            'algolia3',
            'algolia4',
            'meilisearch',
            'typesense',
            'null',
        ]);

        $column = $this->column ?? $property;

        if ($descending) {
            $query->oldest($column);

            return;
        }

        $query->latest($column);
    }
}
