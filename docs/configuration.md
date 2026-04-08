# Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="scout-builder-config"
```

## Full Reference

```php
// config/scout-builder.php

return [
    /*
    |--------------------------------------------------------------------------
    | Request Parameter Names
    |--------------------------------------------------------------------------
    |
    | The HTTP query parameter names used to resolve the search query, filters,
    | and sorts from the incoming request.
    |
    */
    'parameters' => [
        'query'  => 'query',
        'filter' => 'filter',
        'sort'   => 'sort',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Settings for jsonPaginate(). Page number and size are read from
    | page[number] and page[size] query parameters by default.
    |
    */
    'pagination' => [
        'pagination_parameter' => 'page',
        'number_parameter'     => 'number',
        'size_parameter'       => 'size',
        'default_size'         => 30,
        'max_size'             => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Value Delimiter
    |--------------------------------------------------------------------------
    |
    | The character used to split filter values into arrays.
    | For example: ?filter[tags]=php,laravel  →  ['php', 'laravel']
    |
    */
    'delimiter' => ',',

    /*
    |--------------------------------------------------------------------------
    | Disable Exception Throwing
    |--------------------------------------------------------------------------
    |
    | Set to true to silently ignore unknown filter/sort names instead of
    | throwing an InvalidFilterQuery / InvalidSortQuery exception.
    |
    */
    'disable_invalid_filter_query_exception' => false,
    'disable_invalid_sort_query_exception'   => false,

    /*
    |--------------------------------------------------------------------------
    | Engine Awareness
    |--------------------------------------------------------------------------
    |
    | When enforce_support is true, applying a feature on a driver that is not
    | in the corresponding allow-list throws UnsupportedEngineFeature.
    |
    | See docs/engine-awareness.md for details.
    |
    */
    'engine_awareness' => [
        'enforce_support' => false,

        'operator_filter_drivers' => [
            'database', 'collection',
            'algolia', 'algolia3', 'algolia4',
            'meilisearch', 'typesense', 'null',
        ],

        'field_sort_drivers' => [
            'database', 'collection',
            'algolia', 'algolia3', 'algolia4',
            'meilisearch', 'typesense', 'null',
        ],
    ],
];
```
