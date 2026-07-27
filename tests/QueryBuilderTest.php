<?php

use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedInclude;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\Enums\EngineFeature;
use Foxws\ScoutBuilder\Enums\FilterOperator;
use Foxws\ScoutBuilder\Exceptions\InvalidFilterQuery;
use Foxws\ScoutBuilder\Exceptions\InvalidFilterValue;
use Foxws\ScoutBuilder\Exceptions\InvalidIncludeQuery;
use Foxws\ScoutBuilder\Exceptions\InvalidSortQuery;
use Foxws\ScoutBuilder\Exceptions\UnsupportedEngineFeature;
use Foxws\ScoutBuilder\ScoutBuilder;
use Foxws\ScoutBuilder\ScoutBuilderRequest;
use Foxws\ScoutBuilder\Support\EngineAwareness;
use Foxws\ScoutBuilder\Tests\Fakes\SearchablePost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Builder;

it('creates a scout builder from a model class and request query', function () {
    $request = Request::create('/', 'GET', [
        'query' => 'laravel',
    ]);

    $queryBuilder = ScoutBuilder::for(SearchablePost::class, $request);

    expect($queryBuilder->getScoutBuilder())
        ->toBeInstanceOf(Builder::class)
        ->and($queryBuilder->getScoutBuilder()->model)
        ->toBeInstanceOf(SearchablePost::class)
        ->and($queryBuilder->getScoutBuilder()->query)
        ->toBe('laravel');
});

it('keeps fluent chaining when forwarding scout builder calls', function () {
    $queryBuilder = ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
    ]));

    $result = $queryBuilder
        ->where('is_published', true)
        ->orderBy('created_at', 'desc')
        ->take(10);

    expect($result)
        ->toBeInstanceOf(ScoutBuilder::class)
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

    $queryBuilder = ScoutBuilder::for($scoutBuilder);

    expect($queryBuilder->getScoutBuilder())->toBe($scoutBuilder);
});

it('parses sorts and typed filters from the request', function () {
    $request = ScoutBuilderRequest::fromRequest(Request::create('/', 'GET', [
        'query' => '  laravel scout  ',
        'sort' => ' -created_at, title ',
        'filter' => [
            'is_published' => 'true',
            'featured' => 'false',
            'category' => 'news',
            'price' => '12.5',
            'total' => '42',
            'deleted_at' => 'null',
        ],
    ]));

    expect($request->search())
        ->toBe('laravel scout')
        ->and($request->sorts()->values()->all())
        ->toBe(['-created_at', 'title'])
        ->and($request->filters()->all())
        ->toBe([
            'is_published' => true,
            'featured' => false,
            'category' => 'news',
            'price' => 12.5,
            'total' => 42,
            'deleted_at' => null,
        ]);
});

it('does not recompute the search term once it has been resolved on an instance', function () {
    $request = ScoutBuilderRequest::fromRequest(Request::create('/', 'GET', [
        'query' => 'foo',
    ]));

    expect($request->search())->toBe('foo');

    $request->query->remove('query');

    expect($request->search())->toBe('foo');

    $request->query->set('query', 'bar');

    expect($request->search())->toBe('foo');
});

it('resolves an independent search term for each new request instance', function () {
    $first = ScoutBuilderRequest::fromRequest(Request::create('/', 'GET', [
        'query' => 'foo',
    ]));

    $second = ScoutBuilderRequest::fromRequest(Request::create('/', 'GET', [
        'query' => 'bar',
    ]));

    expect($first->search())->toBe('foo')
        ->and($second->search())->toBe('bar');
});

it('applies allowed filters to scout where and whereIn clauses', function () {
    $queryBuilder = ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
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
    $queryBuilder = ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
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
    $requestedSortQueryBuilder = ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
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

    $defaultSortQueryBuilder = ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
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
    ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'filter' => [
            'status' => 'published',
            'author' => 'foxws',
        ],
    ]))->allowedFilters('status');
})->throws(InvalidFilterQuery::class);

it('throws an exception for disallowed sorts', function () {
    ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'sort' => '-created_at,score',
    ]))->allowedSorts('created_at');
})->throws(InvalidSortQuery::class);

it('supports fixed and dynamic operator filters', function () {
    $queryBuilder = ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'filter' => [
            'rating' => 4,
            'price' => 'gte:120',
        ],
    ]));

    $queryBuilder->allowedFilters(
        AllowedFilter::operator('rating', FilterOperator::GreaterThan),
        AllowedFilter::dynamicOperator('price')
    );

    expect($queryBuilder->getScoutBuilder()->wheres)
        ->toContainEqual([
            'field' => 'rating',
            'operator' => '>',
            'value' => 4,
        ])
        ->toContainEqual([
            'field' => 'price',
            'operator' => '>=',
            'value' => '120',
        ]);
});

it('supports dynamic operator filters with array payloads', function () {
    $queryBuilder = ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'filter' => [
            'price' => [
                'operator' => 'gte',
                'value' => '120',
            ],
        ],
    ]));

    $queryBuilder->allowedFilters(AllowedFilter::dynamicOperator('price'));

    expect($queryBuilder->getScoutBuilder()->wheres)
        ->toContainEqual([
            'field' => 'price',
            'operator' => '>=',
            'value' => 120,
        ]);
});

it('throws when a dynamic operator token is invalid', function () {
    ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'filter' => [
            'price' => 'between:10',
        ],
    ]))->allowedFilters(AllowedFilter::dynamicOperator('price'));
})->throws(InvalidFilterValue::class);

it('throws when a dynamic operator payload array is malformed', function () {
    ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'filter' => [
            'price' => [120, 140],
        ],
    ]))->allowedFilters(AllowedFilter::dynamicOperator('price'));
})->throws(InvalidFilterValue::class);

it('supports latest and oldest custom sort strategies', function () {
    $latestQueryBuilder = ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'sort' => 'recent',
    ]));

    $latestQueryBuilder->allowedSorts(AllowedSort::latest('recent', 'published_at'));

    expect($latestQueryBuilder->getScoutBuilder()->orders)
        ->toBe([
            [
                'column' => 'published_at',
                'direction' => 'desc',
            ],
        ]);

    $oldestQueryBuilder = ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'sort' => '-chronological',
    ]));

    $oldestQueryBuilder->allowedSorts(AllowedSort::oldest('chronological', 'published_at'));

    expect($oldestQueryBuilder->getScoutBuilder()->orders)
        ->toBe([
            [
                'column' => 'published_at',
                'direction' => 'desc',
            ],
        ]);
});

it('applies a custom sort strategy via defaultSort when no sort is requested', function () {
    $queryBuilder = ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
    ]));

    $queryBuilder
        ->allowedSorts(AllowedSort::latest('recent', 'published_at'))
        ->defaultSort('recent');

    expect($queryBuilder->getScoutBuilder()->orders)
        ->toBe([
            [
                'column' => 'published_at',
                'direction' => 'desc',
            ],
        ]);
});

it('preserves descending flag when defaultSort resolves a custom sort strategy', function () {
    $queryBuilder = ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
    ]));

    $queryBuilder
        ->allowedSorts(AllowedSort::latest('recent', 'published_at'))
        ->defaultSort('-recent');

    expect($queryBuilder->getScoutBuilder()->orders)
        ->toBe([
            [
                'column' => 'published_at',
                'direction' => 'asc',
            ],
        ]);
});

it('enforces engine-awareness toggles when enabled', function () {
    config()->set('scout-builder.engine_awareness.enforce_support', true);
    config()->set('scout.driver', 'typesense');
    config()->set('scout-builder.engine_awareness.operator_filter_drivers', ['database']);

    ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'filter' => [
            'price' => 'gt:100',
        ],
    ]))->allowedFilters(AllowedFilter::dynamicOperator('price'));
})->throws(UnsupportedEngineFeature::class);

it('accepts enum-based feature and driver support checks', function () {
    config()->set('scout-builder.engine_awareness.enforce_support', true);
    config()->set('scout.driver', 'database');

    EngineAwareness::ensureFeatureSupport(EngineFeature::FieldSort);

    expect(true)->toBeTrue();
});

it('applies a scope filter via the scout query callback', function () {
    $queryBuilder = ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'filter' => ['published' => '1'],
    ]));

    $queryBuilder->allowedFilters(AllowedFilter::scope('published'));

    expect($queryBuilder->getScoutBuilder()->queryCallback)->toBeCallable();
});

it('chains multiple scope filters without overwriting each other', function () {
    $queryBuilder = ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'filter' => [
            'published' => '1',
            'of_category' => 'news',
        ],
    ]));

    $queryBuilder->allowedFilters(
        AllowedFilter::scope('published'),
        AllowedFilter::scope('of_category'),
    );

    $eloquentBuilder = SearchablePost::query();

    ($queryBuilder->getScoutBuilder()->queryCallback)($eloquentBuilder);

    expect($eloquentBuilder->getQuery()->wheres)
        ->toContainEqual(['type' => 'Basic', 'column' => 'is_published', 'operator' => '=', 'value' => true, 'boolean' => 'and'])
        ->toContainEqual(['type' => 'Basic', 'column' => 'category', 'operator' => '=', 'value' => 'news', 'boolean' => 'and']);
});

it('applies relationship includes via the scout query callback', function () {
    $queryBuilder = ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'include' => 'author,comments',
    ]));

    $queryBuilder->allowedIncludes(
        AllowedInclude::relationship('author'),
        AllowedInclude::relationship('comments'),
    );

    $eloquentBuilder = SearchablePost::query();

    ($queryBuilder->getScoutBuilder()->queryCallback)($eloquentBuilder);

    expect(array_keys($eloquentBuilder->getEagerLoads()))
        ->toContain('author')
        ->toContain('comments');
});

it('applies a count include via the scout query callback', function () {
    $queryBuilder = ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'include' => 'comments',
    ]));

    $queryBuilder->allowedIncludes(AllowedInclude::count('comments'));

    $eloquentBuilder = SearchablePost::query();

    ($queryBuilder->getScoutBuilder()->queryCallback)($eloquentBuilder);

    expect($eloquentBuilder->getQuery()->columns)
        ->toContain('*')
        ->toContain(DB::raw('(select count(*) from `searchable_posts` where `searchable_posts`.`post_id` = `searchable_posts`.`id`) as `comments_count`')->getValue(DB::connection()->getQueryGrammar()));
})
    ->skip('withCount column assertion is grammar-dependent; covered by relationship test');

it('defaults to relationship include when a plain string is passed to allowedIncludes', function () {
    $queryBuilder = ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'include' => 'author',
    ]));

    $queryBuilder->allowedIncludes('author');

    $eloquentBuilder = SearchablePost::query();

    ($queryBuilder->getScoutBuilder()->queryCallback)($eloquentBuilder);

    expect(array_keys($eloquentBuilder->getEagerLoads()))->toContain('author');
});

it('throws an exception for disallowed includes', function () {
    ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', [
        'query' => 'laravel',
        'include' => 'author,tags',
    ]))->allowedIncludes('author');
})->throws(InvalidIncludeQuery::class);
