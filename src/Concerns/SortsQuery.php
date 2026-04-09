<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Concerns;

use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\Exceptions\InvalidSortQuery;
use Illuminate\Support\Collection;

trait SortsQuery
{
    protected Collection $allowedSorts;

    public function allowedSorts(AllowedSort|string ...$sorts): static
    {
        $this->allowedSorts = collect($sorts)->map(function (AllowedSort|string $sort): AllowedSort {
            if ($sort instanceof AllowedSort) {
                return $sort;
            }

            return AllowedSort::field(ltrim($sort, '-'));
        });

        $this->ensureAllSortsExist();
        $this->addRequestedSortsToQuery();

        return $this;
    }

    public function defaultSort(AllowedSort|string ...$sorts): static
    {
        return $this->defaultSorts(...$sorts);
    }

    public function defaultSorts(AllowedSort|string ...$sorts): static
    {
        if ($this->request->sorts()->isNotEmpty()) {
            return $this;
        }

        collect($sorts)
            ->map(function (AllowedSort|string $sort): AllowedSort {
                if ($sort instanceof AllowedSort) {
                    return $sort;
                }

                return $this->findSort(ltrim($sort, '-')) ?? AllowedSort::field($sort);
            })
            ->each(function (AllowedSort $sort): void {
                $sort->sort($this);
            });

        return $this;
    }

    protected function addRequestedSortsToQuery(): void
    {
        $this->request->sorts()->each(function (string $property): void {
            $descending = str_starts_with($property, '-');
            $key = ltrim($property, '-');
            $sort = $this->findSort($key);

            $sort?->sort($this, $descending);
        });
    }

    protected function findSort(string $property): ?AllowedSort
    {
        return $this->allowedSorts->first(fn (AllowedSort $sort): bool => $sort->isSort($property));
    }

    protected function ensureAllSortsExist(): void
    {
        if (config('scout-builder.disable_invalid_sort_query_exception', false)) {
            return;
        }

        $requestedSortNames = $this->request->sorts()->map(fn (string $sort): string => ltrim($sort, '-'));
        $allowedSortNames = $this->allowedSorts->map(fn (AllowedSort $sort): string => $sort->getName());
        $unknownSorts = $requestedSortNames->diff($allowedSortNames);

        if ($unknownSorts->isNotEmpty()) {
            throw InvalidSortQuery::sortsNotAllowed($unknownSorts, $allowedSortNames);
        }
    }
}
