---
sidebar_position: 3
---

# Usage

## Quick Start

Add the `Searchable` trait to your model as usual, then build a search
endpoint:

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

This reads directly from the incoming `$request`:

| Parameter           | Example                     |
| -------------------- | ---------------------------- |
| Search query         | `?query=laravel`             |
| Exact filter         | `?filter[status]=published`  |
| Multi-value filter   | `?filter[tags]=php,laravel`  |
| Operator filter      | `?filter[price]=gte:100`     |
| Sort                 | `?sort=-recent,title`        |
| Paginate             | `?page[number]=2&page[size]=15` |

See [Pagination](./pagination.md) for the full `jsonPaginate()` reference.

## Wrapping an Existing Scout Builder

```php
$builder = Post::search('laravel')->where('is_published', true);

$results = ScoutBuilder::for($builder, $request)
    ->allowedFilters(AllowedFilter::exact('status'))
    ->get();
```

## Facade

```php
use Foxws\ScoutBuilder\Facades\ScoutBuilder;

$results = ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::scope('published'))
    ->get();
```

## Differences from spatie/laravel-query-builder

| Feature                                       | spatie/laravel-query-builder | foxws/laravel-scout-builder                                |
| ---------------------------------------------- | ----------------------------- | ------------------------------------------------------------ |
| Underlying builder                             | Eloquent `Builder`            | Scout `Builder`                                               |
| `AllowedInclude`                               | ✅                             | ✅ via Scout `query()` callback (database/collection drivers) |
| `FiltersPartial`, `FiltersBeginsWith`, etc.    | ✅                             | — (text search handled by Scout itself)                      |
| `AllowedFilter::operator()`                    | via `FiltersOperator`         | ✅ first-class with `FilterOperator` enum                     |
| `AllowedFilter::dynamicOperator()`             | —                              | ✅ colon-token + array payload                                |
| `AllowedFilter::notIn()`                       | —                              | ✅                                                             |
| `AllowedSort::latest()` / `oldest()`           | —                              | ✅                                                             |
| `jsonPaginate()`                               | ✅ (Eloquent only)             | ✅ JSON:API `page[number]`/`page[size]`                        |
| Engine awareness                               | —                              | ✅ `ScoutDriver` + `EngineFeature` enums                       |
| Request scalar casting                         | raw strings                   | ✅ auto-casts `'true'`, `'42'`, `'null'`, etc.                 |
