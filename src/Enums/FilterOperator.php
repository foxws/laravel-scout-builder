<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Enums;

enum FilterOperator: string
{
    case Equal = '=';
    case NotEqual = '!=';
    case LessThan = '<';
    case LessThanOrEqual = '<=';
    case GreaterThan = '>';
    case GreaterThanOrEqual = '>=';

    public static function fromToken(string $token): ?self
    {
        return match (strtolower($token)) {
            'eq' => self::Equal,
            'neq', 'ne' => self::NotEqual,
            'lt' => self::LessThan,
            'lte' => self::LessThanOrEqual,
            'gt' => self::GreaterThan,
            'gte' => self::GreaterThanOrEqual,
            default => null,
        };
    }
}
