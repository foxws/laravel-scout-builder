<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Filters;

use Foxws\ScoutBuilder\Enums\FilterOperator;
use Foxws\ScoutBuilder\Exceptions\InvalidFilterValue;
use Foxws\ScoutBuilder\Support\EngineAwareness;
use Laravel\Scout\Builder;

class FiltersOperator implements Filter
{
    public function __construct(protected ?FilterOperator $operator = null) {}

    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        EngineAwareness::ensureFeatureSupport('operator_filter', [
            'database',
            'collection',
            'algolia',
            'algolia3',
            'algolia4',
            'meilisearch',
            'typesense',
            'null',
        ]);

        [$operator, $operand] = $this->resolveOperatorAndOperand($value);

        $query->where($property, $operator->value, $operand);
    }

    /**
     * @return array{FilterOperator, mixed}
     */
    protected function resolveOperatorAndOperand(mixed $value): array
    {
        if ($this->operator instanceof FilterOperator) {
            return [$this->operator, $value];
        }

        if (! is_string($value) || ! str_contains($value, ':')) {
            return [FilterOperator::Equal, $value];
        }

        [$token, $operand] = explode(':', $value, 2);
        $operator = FilterOperator::fromToken($token);

        if (! $operator instanceof FilterOperator) {
            throw InvalidFilterValue::invalidOperator($token);
        }

        return [$operator, $operand];
    }
}
