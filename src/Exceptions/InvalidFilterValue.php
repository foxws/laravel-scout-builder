<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Exceptions;

final class InvalidFilterValue extends InvalidQuery
{
    public static function multipleValues(): static
    {
        return new self('This filter accepts a single value. Use an `in` filter to accept several.');
    }

    public static function typesenseSyntax(): static
    {
        return new self('Filter values can\'t contain Typesense filter syntax, like `.{` or `:(`.');
    }

    public static function invalidOperator(string $token): static
    {
        return new self("Filter operator `{$token}` is not supported. Allowed operator tokens are `eq`, `neq`, `lt`, `lte`, `gt`, and `gte`.");
    }

    public static function invalidOperatorPayload(): static
    {
        return new self('Dynamic operator filters expect either a scalar value like `gte:10` or an array payload with `operator` and `value` keys.');
    }
}
