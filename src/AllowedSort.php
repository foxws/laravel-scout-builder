<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder;

use Foxws\ScoutBuilder\Sorts\Sort;
use Foxws\ScoutBuilder\Sorts\SortsCallback;
use Foxws\ScoutBuilder\Sorts\SortsField;
use Foxws\ScoutBuilder\Sorts\SortsLatest;
use Foxws\ScoutBuilder\Sorts\SortsOldest;

class AllowedSort
{
    protected bool $defaultDescending;

    protected string $internalName;

    public function __construct(
        protected string $name,
        protected Sort $sortClass,
        ?string $internalName = null,
    ) {
        $this->name = ltrim($name, '-');
        $this->defaultDescending = str_starts_with($name, '-');
        $this->internalName = $internalName ?? $this->name;
    }

    public static function field(string $name, ?string $internalName = null): static
    {
        return new static($name, new SortsField, $internalName);
    }

    public static function callback(string $name, callable $callback, ?string $internalName = null): static
    {
        return new static($name, new SortsCallback($callback), $internalName);
    }

    public static function custom(string $name, Sort $sortClass, ?string $internalName = null): static
    {
        return new static($name, $sortClass, $internalName);
    }

    public static function latest(string $name = 'latest', ?string $column = null): static
    {
        return new static($name, new SortsLatest($column), $column ?? $name);
    }

    public static function oldest(string $name = 'oldest', ?string $column = null): static
    {
        return new static($name, new SortsOldest($column), $column ?? $name);
    }

    public function sort(ScoutBuilder $query, ?bool $descending = null): void
    {
        $descending = $descending ?? $this->defaultDescending;

        ($this->sortClass)($query->getScoutBuilder(), $descending, $this->internalName);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isSort(string $sortName): bool
    {
        return $this->name === $sortName;
    }

    public function getInternalName(): string
    {
        return $this->internalName;
    }

    public function defaultDescending(bool $defaultDescending = true): static
    {
        $this->defaultDescending = $defaultDescending;

        return $this;
    }
}
