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

    protected function getRequestData(?string $key = null, mixed $default = null): mixed
    {
        return $this->input($key, $default);
    }

    protected function delimiter(): string
    {
        return (string) Config::get('scout-builder.delimiter', ',');
    }
}
