<?php

use Foxws\ScoutBuilder\ScoutBuilder;
use Foxws\ScoutBuilder\Tests\Fakes\SearchablePost;
use Illuminate\Http\Request;

beforeEach(function () {
    config()->set('scout.driver', 'null');
    config()->set('scout-builder.pagination.default_size', 15);
    config()->set('scout-builder.pagination.max_size', 30);
});

function pageSizeFor(array $page, ?int $maxResults = null, ?int $defaultSize = null): int
{
    return ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', ['page' => $page]))
        ->jsonPaginate($maxResults, $defaultSize)
        ->perPage();
}

it('uses the requested page size', function () {
    expect(pageSizeFor(['size' => 20]))->toBe(20);
});

it('uses the configured default size when no size is requested', function () {
    expect(pageSizeFor([]))->toBe(15);
});

it('limits the page size to the configured maximum', function () {
    expect(pageSizeFor(['size' => 500]))->toBe(30);
});

it('allows a larger page size up to the given maximum', function () {
    expect(pageSizeFor(['size' => 80], maxResults: 100))->toBe(80)
        ->and(pageSizeFor(['size' => 500], maxResults: 100))->toBe(100);
});

it('uses the given default size when no size is requested', function () {
    expect(pageSizeFor([], maxResults: 100, defaultSize: 50))->toBe(50);
});

it('uses the default size for an invalid requested size', function (mixed $size) {
    expect(pageSizeFor(['size' => $size]))->toBe(15);
})->with([
    'zero' => 0,
    'negative' => -5,
    'not a number' => 'abc',
]);

it('also applies the page size to simple pagination', function () {
    expect(
        ScoutBuilder::for(SearchablePost::class, Request::create('/', 'GET', ['page' => ['size' => 80]]))
            ->jsonSimplePaginate(maxResults: 100)
            ->perPage()
    )->toBe(80);
});
