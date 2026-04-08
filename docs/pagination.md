# Pagination

`ScoutBuilder` supports JSON:API-style pagination via dedicated methods. All methods read `page[number]` and `page[size]` from the request and delegate to Scout's native paginators.

## Methods

| Method | Scout equivalent | Returns |
|---|---|---|
| `jsonPaginate()` | `paginate()` | `LengthAwarePaginator` — includes total count |
| `jsonSimplePaginate()` | `simplePaginate()` | `Paginator` — next/prev only, more efficient |

> Note: Scout does not support cursor pagination (`cursorPaginate`). Use `jsonSimplePaginate()` for the most efficient option when total counts are not needed.

## Basic Usage

```php
use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;

// Full pagination with total count
$results = ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::exact('status'))
    ->allowedSorts(AllowedSort::field('title'))
    ->jsonPaginate();

// Simple pagination (next/prev only — more efficient)
$results = ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::exact('status'))
    ->allowedSorts(AllowedSort::field('title'))
    ->jsonSimplePaginate();
```

Both return standard Laravel paginators, so they work directly with Eloquent API Resources and Inertia props.

## Query Parameters

| Parameter | Description | Default |
|---|---|---|
| `page[number]` | The page to fetch | `1` |
| `page[size]` | Number of results per page | `30` |

Example request:

```
GET /posts?query=laravel&filter[status]=published&sort=title&page[number]=2&page[size]=15
```

## Overriding Defaults Per-Call

You can override `max_size` and `default_size` for a specific endpoint:

```php
$results = ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::exact('status'))
    ->jsonPaginate(maxResults: 100, defaultSize: 50);
```

## Configuration

The parameter names and default sizes are defined in `config/scout-builder.php`:

```php
'pagination' => [
    'pagination_parameter' => 'page',    // the outer key: page[...]
    'number_parameter'     => 'number',  // page[number]
    'size_parameter'       => 'size',    // page[size]
    'default_size'         => 30,
    'max_size'             => 30,
],
```

Publish the config to customise these values:

```bash
php artisan vendor:publish --tag="scout-builder-config"
```
