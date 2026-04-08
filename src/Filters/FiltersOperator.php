<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Filters;

use Foxws\ScoutBuilder\Enums\EngineFeature;
use Foxws\ScoutBuilder\Enums\FilterOperator;
use Foxws\ScoutBuilder\Enums\ScoutDriver;
use Foxws\ScoutBuilder\Exceptions\InvalidFilterValue;
use Foxws\ScoutBuilder\Support\EngineAwareness;
use Laravel\Scout\Builder;

class FiltersOperator implements Filter
{
    public function __construct(protected ?FilterOperator $operator = null) {}

    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        EngineAwareness::ensureFeatureSupport(EngineFeature::OperatorFilter, ScoutDriver::cases());

        [$operator, $operand] = $this->resolveOperatorAndOperand($value);

        $query->where($property, $operator->value, $operand);
    }

    /**
     * @return array{FilterOperator, mixed}
     */
    protected function resolveOperatorAndOperand(mixed $value): array
    {
        if ($this->operator instanceof FilterOperator) {
            if (is_array($value) && array_key_exists('value', $value)) {
                return [$this->operator, $value['value']];
            }

            return [$this->operator, $value];
        }

        if (is_array($value) && array_key_exists('operator', $value) && array_key_exists('value', $value)) {
            $operator = FilterOperator::fromToken((string) $value['operator']);

            if (! $operator instanceof FilterOperator) {
                throw InvalidFilterValue::invalidOperator((string) $value['operator']);
            }

            return [$operator, $value['value']];
        }

        if (is_array($value)) {
            throw InvalidFilterValue::invalidOperatorPayload();
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
