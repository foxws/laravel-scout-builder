<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class ScoutBuilderRequest extends Request
{
    public static function fromRequest(Request $request): static
    {
        return static::createFrom($request, new static);
    }

    public function search(): string
    {
        $queryParameterName = (string) Config::get('scout-builder.parameters.query', 'query');

        return trim((string) $this->getRequestData($queryParameterName, ''));
    }

    public function sorts(): Collection
    {
        $sortParameterName = (string) Config::get('scout-builder.parameters.sort', 'sort');

        $sortParts = $this->getRequestData($sortParameterName);

        if (is_string($sortParts)) {
            $sortParts = explode($this->delimiter(), $sortParts);
        }

        return collect($sortParts)
            ->map(fn (mixed $sort): mixed => is_string($sort) ? trim($sort) : $sort)
            ->filter();
    }

    public function includes(): Collection
    {
        $includeParameterName = (string) Config::get('scout-builder.parameters.include', 'include');

        $includeParts = $this->getRequestData($includeParameterName);

        if (is_string($includeParts)) {
            $includeParts = explode($this->delimiter(), $includeParts);
        }

        return collect($includeParts)
            ->map(fn (mixed $include): mixed => is_string($include) ? trim($include) : $include)
            ->filter();
    }

    public function filters(): Collection
    {
        $filterParameterName = (string) Config::get('scout-builder.parameters.filter', 'filter');

        $filterParts = $this->getRequestData($filterParameterName, []);

        if (is_string($filterParts)) {
            return collect();
        }

        $filters = collect($filterParts);

        return $filters->map(function (mixed $value): mixed {
            return $this->getFilterValue($value);
        });
    }

    protected function getFilterValue(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (is_array($value)) {
            return collect($value)
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

    protected function delimiter(): string
    {
        return (string) Config::get('scout-builder.delimiter', ',');
    }
}
