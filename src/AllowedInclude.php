<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder;

use Foxws\ScoutBuilder\Includes\Includable;
use Foxws\ScoutBuilder\Includes\IncludesCallback;
use Foxws\ScoutBuilder\Includes\IncludesCount;
use Foxws\ScoutBuilder\Includes\IncludesRelationship;

class AllowedInclude
{
    protected string $internalName;

    public function __construct(
        protected string $name,
        protected Includable $includeClass,
        ?string $internalName = null,
    ) {
        $this->internalName = $internalName ?? $name;
    }

    public static function relationship(string $name, ?string $internalName = null): static
    {
        return new static($name, new IncludesRelationship, $internalName);
    }

    public static function count(string $name, ?string $internalName = null): static
    {
        return new static($name, new IncludesCount, $internalName);
    }

    public static function callback(string $name, callable $callback, ?string $internalName = null): static
    {
        return new static($name, new IncludesCallback($callback), $internalName);
    }

    public static function custom(string $name, Includable $includeClass, ?string $internalName = null): static
    {
        return new static($name, $includeClass, $internalName);
    }

    public function include(ScoutBuilder $query): void
    {
        ($this->includeClass)($query->getScoutBuilder(), $this->internalName);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isForInclude(string $includeName): bool
    {
        return $this->name === $includeName;
    }

    public function getInternalName(): string
    {
        return $this->internalName;
    }
}
