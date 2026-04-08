<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Exceptions;

final class InvalidFilterValue extends InvalidQuery
{
    public static function invalidOperator(string $token): static
    {
        return new static("Filter operator `{$token}` is not supported. Allowed operator tokens are `eq`, `neq`, `lt`, `lte`, `gt`, and `gte`.");
    }

    public static function invalidOperatorPayload(): static
    {
        return new static('Dynamic operator filters expect either a scalar value like `gte:10` or an array payload with `operator` and `value` keys.');
    }
}
