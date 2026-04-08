<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder;

use Foxws\ScoutBuilder\Enums\FilterOperator;
use Foxws\ScoutBuilder\Filters\Filter;
use Foxws\ScoutBuilder\Filters\FiltersCallback;
use Foxws\ScoutBuilder\Filters\FiltersExact;
use Foxws\ScoutBuilder\Filters\FiltersIn;
use Foxws\ScoutBuilder\Filters\FiltersNotIn;
use Foxws\ScoutBuilder\Filters\FiltersOperator;
use Foxws\ScoutBuilder\Filters\FiltersScope;
use Foxws\ScoutBuilder\Filters\FiltersTrashed;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class AllowedFilter
{
    protected string $internalName;

    protected Collection $ignored;

    protected mixed $default = null;

    protected bool $hasDefault = false;

    protected bool $nullable = false;

    protected ?string $arrayValueDelimiter = null;

    public function __construct(
        protected string $name,
        protected Filter $filterClass,
        ?string $internalName = null,
    ) {
        $this->ignored = Collection::make();
        $this->internalName = $internalName ?? $name;
    }

    public static function exact(string $name, ?string $internalName = null): static
    {
        return new static($name, new FiltersExact, $internalName);
    }

    public static function in(string $name, ?string $internalName = null): static
    {
        return new static($name, new FiltersIn, $internalName);
    }

    public static function notIn(string $name, ?string $internalName = null): static
    {
        return new static($name, new FiltersNotIn, $internalName);
    }

    public static function trashed(string $name = 'trashed', ?string $internalName = null): static
    {
        return new static($name, new FiltersTrashed, $internalName);
    }

    public static function callback(string $name, callable $callback, ?string $internalName = null): static
    {
        return new static($name, new FiltersCallback($callback), $internalName);
    }

    public static function custom(string $name, Filter $filterClass, ?string $internalName = null): static
    {
        return new static($name, $filterClass, $internalName);
    }

    public static function operator(string $name, FilterOperator $operator, ?string $internalName = null): static
    {
        return new static($name, new FiltersOperator($operator), $internalName);
    }

    public static function dynamicOperator(string $name, ?string $internalName = null): static
    {
        return new static($name, new FiltersOperator, $internalName);
    }

    public static function scope(string $name, ?string $internalName = null): static
    {
        return new static($name, new FiltersScope, $internalName);
    }

    public function filter(ScoutBuilder $query, mixed $value): void
    {
        $value = $this->splitFilterValue($value);
        $valueToFilter = $this->resolveValueForFiltering($value);

        if (! $this->nullable && is_null($valueToFilter)) {
            return;
        }

        ($this->filterClass)($query->getScoutBuilder(), $valueToFilter, $this->internalName);
    }

    public function delimiter(string $delimiter): static
    {
        $this->arrayValueDelimiter = $delimiter;

        return $this;
    }

    public function getDelimiter(): string
    {
        if (is_string($this->arrayValueDelimiter)) {
            return $this->arrayValueDelimiter;
        }

        // @phpstan-ignore-next-line
        return (string) Config::get('scout-builder.delimiter', ',');
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isForFilter(string $filterName): bool
    {
        return $this->name === $filterName;
    }

    public function ignore(mixed ...$values): static
    {
        $this->ignored = $this->ignored
            ->merge($values)
            ->flatten();

        return $this;
    }

    public function getIgnored(): array
    {
        return $this->ignored->toArray();
    }

    public function getInternalName(): string
    {
        return $this->internalName;
    }

    public function default(mixed $value): static
    {
        $this->hasDefault = true;
        $this->default = $value;

        if (is_null($value)) {
            $this->nullable(true);
        }

        return $this;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function hasDefault(): bool
    {
        return $this->hasDefault;
    }

    public function nullable(bool $nullable = true): static
    {
        $this->nullable = $nullable;

        return $this;
    }

    protected function splitFilterValue(mixed $value): mixed
    {
        $delimiter = $this->getDelimiter();

        if ($delimiter === '') {
            return $value;
        }

        if (is_array($value)) {
            return array_map(fn (mixed $arrayValue): mixed => $this->splitFilterValue($arrayValue), $value);
        }

        if (is_string($value) && Str::contains($value, $delimiter)) {
            return explode($delimiter, $value);
        }

        return $value;
    }

    protected function resolveValueForFiltering(mixed $value): mixed
    {
        if (is_array($value)) {
            $remainingProperties = array_filter(
                array_map([$this, 'resolveValueForFiltering'], $value),
                static fn (mixed $property): bool => $property !== null,
            );

            if ($remainingProperties === []) {
                return null;
            }

            return array_is_list($value)
                ? array_values($remainingProperties)
                : $remainingProperties;
        }

        return ! $this->ignored->contains($value) ? $value : null;
    }
}
