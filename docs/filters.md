---
section: Usage
order: 2
---

# Filters

Filters come from the `filter` parameter on the request. You have to
explicitly allow each filter you want to accept — any filter name that
isn't allowed throws an `InvalidFilterQuery` exception.

## Exact

Matches a field exactly, using Scout's `where()`.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::exact('status'));
```

Request: `?filter[status]=published`

## In

Matches any value from a comma-separated list, using Scout's `whereIn()`.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::in('tags'));
```

Request: `?filter[tags]=php,laravel`

## Not In

The opposite of `in` — excludes matching values, using Scout's
`whereNotIn()`.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::notIn('tags'));
```

Request: `?filter[tags]=spam,draft`

## Trashed

Controls whether soft-deleted records are included in the results.

| Value | Behaviour |
|---|---|
| `with` | Include trashed records alongside normal ones |
| `only` | Return only trashed records |
| anything else | Default behaviour (no trashed records) |

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::trashed());
```

Request: `?filter[trashed]=only`

## Fixed Operator

Compares a field using one operator that you fix in code up front.

```php
use Foxws\ScoutBuilder\Enums\FilterOperator;

ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(
        AllowedFilter::operator('rating', FilterOperator::GreaterThan),
    );
```

Request: `?filter[rating]=4`

`FilterOperator` cases:

| Case | Meaning |
|---|---|
| `Equal` | `=` |
| `NotEqual` | `!=` |
| `LessThan` | `<` |
| `LessThanOrEqual` | `<=` |
| `GreaterThan` | `>` |
| `GreaterThanOrEqual` | `>=` |

## Dynamic Operator

Lets the client choose the operator at request time, instead of fixing it
in code. It accepts three formats:

| Format | Example |
|---|---|
| Colon-token string | `?filter[price]=gte:120` |
| Array payload | `?filter[price][operator]=gte&filter[price][value]=120` |
| Plain scalar (falls back to `=`) | `?filter[price]=120` |

Available tokens: `eq`, `neq` (or `ne`), `lt`, `lte`, `gt`, `gte`.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::dynamicOperator('price'));
```

An unrecognised token (e.g. `between:10,20`) throws `InvalidFilterValue`.

## Scope

Calls a named Eloquent scope on your model, through Scout's `query()`
callback. Multiple scopes chain together without overwriting each other.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(
        AllowedFilter::scope('published'),
        AllowedFilter::scope('of_category'),
    );
```

Request: `?filter[published]=1&filter[of_category]=news`

The filter name is converted to camelCase, so `of_category` calls
`scopeOfCategory`.

> **Note:** Remote search engines (Algolia, Typesense, Meilisearch) don't
> run Eloquent queries, so they silently ignore scope filters. Only use this
> filter with the `database` or `collection` driver — or add engine-awareness
> enforcement so a mismatch gets caught early (see
> [Engine Awareness](engine-awareness.md)).

## Callback

Write your own filter logic inline, with a closure.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(
        AllowedFilter::callback('search', function (Builder $query, mixed $value): void {
            $query->where('title', $value)->orWhere('body', $value);
        }),
    );
```

## Custom Filter Class

For filter logic you want to reuse, implement the `Filter` interface
instead of writing a closure.

```php
use Foxws\ScoutBuilder\Filters\Filter;
use Laravel\Scout\Builder;

class FiltersPopular implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $query->where('views', '>', (int) $value);
    }
}

ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::custom('popular', new FiltersPopular));
```

## Modifiers

You can chain these onto any `AllowedFilter` to fine-tune its behaviour:

| Modifier | What it does |
|---|---|
| `->default(...)` | Value used when the filter is missing from the request |
| `->nullable()` | Allow `null` through (skipped by default) |
| `->ignore(...)` | Silently skip specific values |
| `->delimiter(...)` | Change the multi-value separator (default `,`) |

```php
AllowedFilter::exact('status')
    ->default('published')    // applied when the filter is absent from the request
    ->nullable()              // allow null to pass through (skipped by default)
    ->ignore('draft', 'spam') // silently skip these values
    ->delimiter('|')          // override the multi-value delimiter (default: ,)
```
