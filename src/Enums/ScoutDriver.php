<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Enums;

enum ScoutDriver: string
{
    case Database = 'database';
    case Collection = 'collection';
    case Algolia = 'algolia';
    case Algolia3 = 'algolia3';
    case Algolia4 = 'algolia4';
    case Meilisearch = 'meilisearch';
    case Typesense = 'typesense';
    case Null = 'null';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $driver): string => $driver->value,
            self::cases(),
        );
    }
}
