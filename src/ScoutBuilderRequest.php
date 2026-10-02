<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class ScoutBuilderRequest extends Request
{
    protected ?string $cachedSearch = null;

    /** @var Collection<int, string>|null */
    protected ?Collection $cachedSorts = null;

    /** @var Collection<int, string>|null */
    protected ?Collection $cachedIncludes = null;

    /** @var Collection<int|string, mixed>|null */
    protected ?Collection $cachedFilters = null;

    public static function fromRequest(Request $request): static
    {
        return static::createFrom($request, new static);
    }

    public function search(): string
    {
        return $this->cachedSearch ??= (function (): string {
            $queryParameterName = (string) Config::get('scout-builder.parameters.query', 'query');

            return trim((string) $this->getRequestData($queryParameterName, ''));
        })();
    }

    /**
     * @return Collection<int, string>
     */
    public function sorts(): Collection
    {
        return $this->cachedSorts ??= (function (): Collection {
            $sortParameterName = (string) Config::get('scout-builder.parameters.sort', 'sort');

            $sortParts = $this->getRequestData($sortParameterName);

            if (is_string($sortParts)) {
                $sortParts = explode($this->delimiter(), $sortParts);
            }

            return Collection::make($this->names($sortParts));
        })();
    }

    /**
     * @return Collection<int, string>
     */
    public function includes(): Collection
    {
        return $this->cachedIncludes ??= (function (): Collection {
            $includeParameterName = (string) Config::get('scout-builder.parameters.include', 'include');

            $includeParts = $this->getRequestData($includeParameterName);

            if (is_string($includeParts)) {
                $includeParts = explode($this->delimiter(), $includeParts);
            }

            return Collection::make($this->names($includeParts));
        })();
    }

    /**
     * @return Collection<int|string, mixed>
     */
    public function filters(): Collection
    {
        return $this->cachedFilters ??= (function (): Collection {
            $filterParameterName = (string) Config::get('scout-builder.parameters.filter', 'filter');

            $filterParts = $this->getRequestData($filterParameterName, []);

            if (! is_array($filterParts)) {
                return Collection::make();
            }

            $filters = Collection::make($filterParts);

            return $filters->map(function (mixed $value): mixed {
                return $this->getFilterValue($value);
            });
        })();
    }

    /**
     * The trimmed, non-empty names in a sort or include parameter. Anything
     * that isn't a string, like a nested array from the query string, is
     * ignored instead of failing the request.
     *
     * @return list<string>
     */
    protected function names(mixed $parts): array
    {
        $names = [];

        foreach (is_array($parts) ? $parts : [] as $part) {
            if (is_string($part) && trim($part) !== '') {
                $names[] = trim($part);
            }
        }

        return $names;
    }

    protected function getFilterValue(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (is_array($value)) {
            return Collection::make($value)
                ->map(function (mixed $innerValue): mixed {
                    return $this->getFilterValue($innerValue);
                })
                ->all();
        }

        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        if ($value === 'null') {
            return null;
        }

        if (is_string($value)) {
            $trimmedValue = trim($value);

            if ($trimmedValue !== '' && preg_match('/^-?\d+$/', $trimmedValue) === 1) {
                return (int) $trimmedValue;
            }

            if ($trimmedValue !== '' && preg_match('/^-?\d+\.\d+$/', $trimmedValue) === 1) {
                return (float) $trimmedValue;
            }

            return $trimmedValue;
        }

        return $value;
    }

    public function pageSize(): int
    {
        $paginationParameter = (string) Config::get('scout-builder.pagination.pagination_parameter', 'page');
        $sizeParameter = (string) Config::get('scout-builder.pagination.size_parameter', 'size');
        $defaultSize = (int) Config::get('scout-builder.pagination.default_size', 30);
        $maxSize = (int) Config::get('scout-builder.pagination.max_size', 30);

        $size = (int) $this->input("{$paginationParameter}.{$sizeParameter}", $defaultSize);

        return min($size, $maxSize);
    }

    public function pageNumber(): int
    {
        $paginationParameter = (string) Config::get('scout-builder.pagination.pagination_parameter', 'page');
        $numberParameter = (string) Config::get('scout-builder.pagination.number_parameter', 'number');

        return max(1, (int) $this->input("{$paginationParameter}.{$numberParameter}", 1));
    }

    protected function getRequestData(?string $key = null, mixed $default = null): mixed
    {
        return $this->input($key, $default);
    }

    /**
     * The configured delimiter, or a comma when it's empty: explode() can't
     * split on an empty string.
     *
     * @return non-empty-string
     */
    protected function delimiter(): string
    {
        $delimiter = (string) Config::get('scout-builder.delimiter', ',');

        return $delimiter !== '' ? $delimiter : ',';
    }
}
