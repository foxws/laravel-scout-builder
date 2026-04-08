<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class QueryBuilderRequest extends Request
{
    public static function fromRequest(Request $request): static
    {
        return static::createFrom($request, new static);
    }

    public function search(): string
    {
        $queryParameterName = Config::string('scout-builder.parameters.query', 'query');

        return (string) $this->getRequestData($queryParameterName, '');
    }

    public function sorts(): Collection
    {
        $sortParameterName = Config::string('scout-builder.parameters.sort', 'sort');

        $sortParts = $this->getRequestData($sortParameterName);

        if (is_string($sortParts)) {
            $sortParts = explode($this->delimiter(), $sortParts);
        }

        return collect($sortParts)->filter();
    }

    public function filters(): Collection
    {
        $filterParameterName = Config::string('scout-builder.parameters.filter', 'filter');

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
        if (empty($value)) {
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

        return $value;
    }

    protected function getRequestData(?string $key = null, mixed $default = null): mixed
    {
        return $this->input($key, $default);
    }

    protected function delimiter(): string
    {
        return Config::string('scout-builder.delimiter', ',');
    }
}
