<?php

use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\Exceptions\InvalidFilterQuery;
use Foxws\ScoutBuilder\Exceptions\InvalidSortQuery;
use Foxws\ScoutBuilder\QueryBuilder;
use Foxws\ScoutBuilder\QueryBuilderRequest;
use Foxws\ScoutBuilder\Tests\Fakes\SearchablePost;
use Illuminate\Http\Request;
use Laravel\Scout\Builder as ScoutBuilder;

it('creates a scout builder from a model class and request query', function () {
    $request = Request::create('/', 'GET', [
        'query' => 'laravel',
    ]);

    $queryBuilder = QueryBuilder::for(SearchablePost::class, $request);

    expect($queryBuilder->getScoutBuilder())
        ->toBeInstanceOf(ScoutBuilder::class)
        ->and($queryBuilder->getScoutBuilder()->model)
        ->toBeInstanceOf(SearchablePost::class)
        ->and($queryBuilder->getScoutBuilder()->query)
        ->toBe('laravel');
});

it('keeps fluent chaining when forwarding scout builder calls', function () {
    $queryBuilder = QueryBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
    ]));

    $result = $queryBuilder
        ->where('is_published', true)
        ->orderBy('created_at', 'desc')
        ->take(10);

    expect($result)
        ->toBeInstanceOf(QueryBuilder::class)
        ->and($result->getScoutBuilder()->wheres)
        ->toContainEqual([
            'field' => 'is_published',
            'operator' => '=',
            'value' => true,
        ])
        ->and($result->getScoutBuilder()->orders)
        ->toContainEqual([
            'column' => 'created_at',
            'direction' => 'desc',
        ])
        ->and($result->getScoutBuilder()->limit)
        ->toBe(10);
});

it('supports wrapping an existing scout builder', function () {
    $scoutBuilder = SearchablePost::search('spatie');

    $queryBuilder = QueryBuilder::for($scoutBuilder);

    expect($queryBuilder->getScoutBuilder())->toBe($scoutBuilder);
});

it('parses sorts and typed filters from the request', function () {
    $request = QueryBuilderRequest::fromRequest(Request::create('/', 'GET', [
        'sort' => '-created_at,title',
        'filter' => [
            'is_published' => 'true',
            'featured' => 'false',
            'category' => 'news',
        ],
    ]));

    expect($request->sorts()->values()->all())
        ->toBe(['-created_at', 'title'])
        ->and($request->filters()->all())
        ->toBe([
            'is_published' => true,
            'featured' => false,
            'category' => 'news',
        ]);
});

it('applies allowed filters to scout where and whereIn clauses', function () {
    $queryBuilder = QueryBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'filter' => [
            'status' => 'published',
            'tags' => 'php,laravel',
        ],
    ]));

    $queryBuilder->allowedFilters(
        AllowedFilter::exact('status'),
        AllowedFilter::in('tags')
    );

    expect($queryBuilder->getScoutBuilder()->wheres)
        ->toContainEqual([
            'field' => 'status',
            'operator' => '=',
            'value' => 'published',
        ])
        ->and($queryBuilder->getScoutBuilder()->whereIns)
        ->toBe([
            'tags' => ['php', 'laravel'],
        ]);
});

it('applies default filters when request filters are absent', function () {
    $queryBuilder = QueryBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
    ]));

    $queryBuilder->allowedFilters(
        AllowedFilter::exact('status')->default('published')
    );

    expect($queryBuilder->getScoutBuilder()->wheres)
        ->toContainEqual([
            'field' => 'status',
            'operator' => '=',
            'value' => 'published',
        ]);
});

it('applies allowed sorts and default sorts to scout orders', function () {
    $requestedSortQueryBuilder = QueryBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'sort' => '-created_at,title',
    ]));

    $requestedSortQueryBuilder->allowedSorts('created_at', 'title');

    expect($requestedSortQueryBuilder->getScoutBuilder()->orders)
        ->toBe([
            [
                'column' => 'created_at',
                'direction' => 'desc',
            ],
            [
                'column' => 'title',
                'direction' => 'asc',
            ],
        ]);

    $defaultSortQueryBuilder = QueryBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
    ]));

    $defaultSortQueryBuilder
        ->allowedSorts(AllowedSort::field('title'))
        ->defaultSort('-created_at');

    expect($defaultSortQueryBuilder->getScoutBuilder()->orders)
        ->toBe([
            [
                'column' => 'created_at',
                'direction' => 'desc',
            ],
        ]);
});

it('throws an exception for disallowed filters', function () {
    QueryBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'filter' => [
            'status' => 'published',
            'author' => 'foxws',
        ],
    ]))->allowedFilters('status');
})->throws(InvalidFilterQuery::class);

it('throws an exception for disallowed sorts', function () {
    QueryBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'sort' => '-created_at,score',
    ]))->allowedSorts('created_at');
})->throws(InvalidSortQuery::class);
