<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Concerns;

use Foxws\ScoutBuilder\AllowedInclude;
use Foxws\ScoutBuilder\Exceptions\InvalidIncludeQuery;
use Illuminate\Support\Collection;

trait IncludesQuery
{
    protected Collection $allowedIncludes;

    public function allowedIncludes(AllowedInclude|string ...$includes): static
    {
        $this->allowedIncludes = Collection::make($includes)->map(function (AllowedInclude|string $include): AllowedInclude {
            if ($include instanceof AllowedInclude) {
                return $include;
            }

            return AllowedInclude::relationship($include);
        });

        $this->ensureAllIncludesExist();
        $this->addIncludesToQuery();

        return $this;
    }

    protected function addIncludesToQuery(): void
    {
        $this->allowedIncludes->each(function (AllowedInclude $include): void {
            if ($this->isIncludeRequested($include)) {
                $include->include($this);
            }
        });
    }

    protected function isIncludeRequested(AllowedInclude $allowedInclude): bool
    {
        return $this->request->includes()->contains($allowedInclude->getName());
    }

    protected function ensureAllIncludesExist(): void
    {
        if (config('scout-builder.disable_invalid_include_query_exception', false)) {
            return;
        }

        $requestedIncludeNames = $this->request->includes();
        $allowedIncludeNames = $this->allowedIncludes->map(fn (AllowedInclude $allowedInclude): string => $allowedInclude->getName());
        $unknownIncludes = $requestedIncludeNames->diff($allowedIncludeNames);

        if ($unknownIncludes->isNotEmpty()) {
            throw InvalidIncludeQuery::includesNotAllowed($unknownIncludes, $allowedIncludeNames);
        }
    }
}
