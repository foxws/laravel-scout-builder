<?php

return [

    /*
     * By default the package will use the `query`, `filter`, `sort`
     * and `include` query parameters as described in the readme.
     *
     * You can customize these query string parameters here.
     */
    'parameters' => [
        'query' => 'query',
        'filter' => 'filter',
        'sort' => 'sort',
        'include' => 'include',
    ],

    /*
     * The delimiter used to split array values in query parameters.
     * For example: ?filter[tags]=php,laravel uses ',' as delimiter.
     */
    'delimiter' => ',',

    /*
     * Pagination settings for the `jsonPaginate()` method.
     *
     * These follow the JSON:API spec, using `page[number]` and `page[size]`
     * query parameters (e.g. `?page[number]=2&page[size]=15`).
     */
    'pagination' => [
        'pagination_parameter' => 'page',
        'number_parameter' => 'number',
        'size_parameter' => 'size',
        'default_size' => 30,
        'max_size' => 30,
    ],

    /*
     * By default the package will throw an `InvalidFilterQuery` exception when a filter in the
     * URL is not allowed in the `allowedFilters()` method.
     */
    'disable_invalid_filter_query_exception' => false,

    /*
     * By default the package will throw an `InvalidSortQuery` exception when a sort in the
     * URL is not allowed in the `allowedSorts()` method.
     */
    'disable_invalid_sort_query_exception' => false,

    /*
     * By default the package will throw an `InvalidIncludeQuery` exception when an include in the
     * URL is not allowed in the `allowedIncludes()` method.
     */
    'disable_invalid_include_query_exception' => false,

    /*
     * Engine awareness allows the package to be aware of the underlying Scout driver in use,
     * and optionally restrict certain filter and sort helpers to only supported drivers.
     *
     * Set `enforce_support` to `true` to throw an exception when an unsupported driver is used.
     */
    'engine_awareness' => [
        'enforce_support' => env('APP_DEBUG', false),

        /*
         * Drivers that support AllowedFilter::operator() and AllowedFilter::dynamicOperator().
         * Only relevant when `enforce_support` is set to `true`.
         */
        'operator_filter_drivers' => [
            'database',
            'collection',
            'algolia',
            'algolia3',
            'algolia4',
            'meilisearch',
            'typesense',
            'null',
        ],

        /*
         * Drivers that support AllowedSort::field(), AllowedSort::latest(), and AllowedSort::oldest().
         * Only relevant when `enforce_support` is set to `true`.
         */
        'field_sort_drivers' => [
            'database',
            'collection',
            'algolia',
            'algolia3',
            'algolia4',
            'meilisearch',
            'typesense',
            'null',
        ],
    ],
];
