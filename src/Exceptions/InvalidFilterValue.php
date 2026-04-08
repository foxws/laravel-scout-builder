<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Exceptions;

class InvalidFilterValue extends InvalidQuery
{
    public static function invalidOperator(string $token): static
    {
        return new static("Filter operator `{$token}` is not supported. Allowed operator tokens are `eq`, `neq`, `lt`, `lte`, `gt`, and `gte`.");
    }
}
