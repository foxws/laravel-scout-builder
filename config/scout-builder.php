<?php

return [
    'parameters' => [
        'query' => 'query',
        'filter' => 'filter',
        'sort' => 'sort',
        'include' => 'include',
    ],

    'delimiter' => ',',

    'disable_invalid_filter_query_exception' => false,
    'disable_invalid_sort_query_exception' => false,
    'disable_invalid_include_query_exception' => false,

    'engine_awareness' => [
        'enforce_support' => false,

        // Drivers allowed for AllowedFilter::operator() when support enforcement is enabled.
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

        // Drivers allowed for field/latest/oldest sort helpers when support enforcement is enabled.
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
