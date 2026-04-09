<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Concerns;

use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\Exceptions\InvalidFilterQuery;
use Illuminate\Support\Collection;

trait FiltersQuery
{
    protected Collection $allowedFilters;

    public function allowedFilters(AllowedFilter|string ...$filters): static
    {
        $this->allowedFilters = Collection::make($filters)->map(function (AllowedFilter|string $filter): AllowedFilter {
            if ($filter instanceof AllowedFilter) {
                return $filter;
            }

            return AllowedFilter::exact($filter);
        });

        $this->ensureAllFiltersExist();
        $this->addFiltersToQuery();

        return $this;
    }

    protected function addFiltersToQuery(): void
    {
        $this->allowedFilters->each(function (AllowedFilter $filter): void {
            if ($this->isFilterRequested($filter)) {
                $value = $this->request->filters()->get($filter->getName());
                $filter->filter($this, $value);

                return;
            }

            if ($filter->hasDefault()) {
                $filter->filter($this, $filter->getDefault());
            }
        });
    }

    protected function isFilterRequested(AllowedFilter $allowedFilter): bool
    {
        return $this->request->filters()->has($allowedFilter->getName());
    }

    protected function ensureAllFiltersExist(): void
    {
        if (config('scout-builder.disable_invalid_filter_query_exception', false)) {
            return;
        }

        $requestedFilterNames = $this->request->filters()->keys();
        $allowedFilterNames = $this->allowedFilters->map(fn (AllowedFilter $allowedFilter): string => $allowedFilter->getName());
        $unknownFilters = $requestedFilterNames->diff($allowedFilterNames);

        if ($unknownFilters->isNotEmpty()) {
            throw InvalidFilterQuery::filtersNotAllowed($unknownFilters, $allowedFilterNames);
        }
    }
}
