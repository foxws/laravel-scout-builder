---
section: Usage
order: 1
---

# Usage

## Quick Start

Add the `Searchable` trait to your model, as you normally would for Scout.
Then build a search endpoint like this:

```php
use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;

$results = ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(
        AllowedFilter::exact('status'),
        AllowedFilter::in('tags'),
        AllowedFilter::dynamicOperator('price'),
    )
    ->allowedSorts(
        AllowedSort::latest('recent', 'published_at'),
        AllowedSort::field('title'),
    )
    ->defaultSort('-recent')
    ->get();
```

`ScoutBuilder` reads everything it needs straight from the incoming
`$request`:

| Parameter | Example |
|---|---|
| Search query | `?query=laravel` |
| Exact filter | `?filter[status]=published` |
| Multi-value filter | `?filter[tags]=php,laravel` |
| Operator filter | `?filter[price]=gte:100` |
| Sort | `?sort=-recent,title` |
| Paginate | `?page[number]=2&page[size]=15` |

See [Pagination](./pagination.md) for everything `jsonPaginate()` can do.

## Wrapping an Existing Scout Builder

Already have a Scout builder with its own conditions on it? Pass that in
instead of a model class:

```php
$builder = Post::search('laravel')->where('is_published', true);

$results = ScoutBuilder::for($builder, $request)
    ->allowedFilters(AllowedFilter::exact('status'))
    ->get();
```

## Facade

You can also reach for the `ScoutBuilder` facade instead of the class
directly:

```php
use Foxws\ScoutBuilder\Facades\ScoutBuilder;

$results = ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::scope('published'))
    ->get();
```

## Differences from spatie/laravel-query-builder

If you're coming from spatie/laravel-query-builder, here's what's different:

| Feature | spatie/laravel-query-builder | foxws/laravel-scout-builder |
|---|---|---|
| Underlying builder | Eloquent `Builder` | Scout `Builder` |
| `AllowedInclude` | Yes | Yes, via Scout's `query()` callback (database/collection drivers only) |
| `FiltersPartial`, `FiltersBeginsWith`, etc. | Yes | No — text search is handled by Scout itself |
| `AllowedFilter::operator()` | Via `FiltersOperator` | Yes, first-class, with a `FilterOperator` enum |
| `AllowedFilter::dynamicOperator()` | No | Yes — colon-token or array payload |
| `AllowedFilter::notIn()` | No | Yes |
| `AllowedSort::latest()` / `oldest()` | No | Yes |
| `jsonPaginate()` | Yes (Eloquent only) | Yes — JSON:API `page[number]`/`page[size]` |
| Engine awareness | No | Yes — `ScoutDriver` + `EngineFeature` enums |
| Request scalar casting | Raw strings | Auto-casts `'true'`, `'42'`, `'null'`, etc. |
