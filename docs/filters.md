# Filters

Filters are applied from the HTTP request's `filter` parameter. Only filters that are explicitly allowed are accepted — any unknown filter names throw an `InvalidFilterQuery` exception.

## Exact

Applies a Scout `where()` with an `=` operator.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::exact('status'));
```

Request: `?filter[status]=published`

## In

Applies a Scout `whereIn()` using a comma-separated list.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::in('tags'));
```

Request: `?filter[tags]=php,laravel`

## Not In

Applies a Scout `whereNotIn()`.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::notIn('tags'));
```

Request: `?filter[tags]=spam,draft`

## Trashed

Includes soft-deleted records. Accepts `with`, `only`, or any other value to restore default behaviour.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::trashed());
```

Request: `?filter[trashed]=only`

## Fixed Operator

Applies a `where()` with a fixed comparison operator.

```php
use Foxws\ScoutBuilder\Enums\FilterOperator;

ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(
        AllowedFilter::operator('rating', FilterOperator::GreaterThan),
    );
```

Request: `?filter[rating]=4`

Available `FilterOperator` cases: `Equal`, `NotEqual`, `LessThan`, `LessThanOrEqual`, `GreaterThan`, `GreaterThanOrEqual`.

## Dynamic Operator

Parses the operator from the filter value at runtime. Supports three input forms:

**Colon-token string:**

```
?filter[price]=gte:120
```

**Array payload:**

```
?filter[price][operator]=gte&filter[price][value]=120
```

**Plain scalar (falls back to `=`):**

```
?filter[price]=120
```

Available tokens: `eq`, `neq` / `ne`, `lt`, `lte`, `gt`, `gte`.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::dynamicOperator('price'));
```

An invalid token (e.g. `between:10,20`) throws `InvalidFilterValue`.

## Scope

Applies a named Eloquent scope via Scout's `query()` callback. Useful for database and collection drivers. Multiple scopes are chained without overwriting each other.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(
        AllowedFilter::scope('published'),
        AllowedFilter::scope('of_category'),
    );
```

Request: `?filter[published]=1&filter[of_category]=news`

The filter name is converted to camelCase (`of_category` → `scopeOfCategory`).

> **Note:** The scope callback is silently ignored by remote engines (Algolia, Typesense, Meilisearch) since they do not execute Eloquent queries. Use this filter only with the `database` or `collection` driver, or add engine-awareness enforcement (see [engine-awareness.md](engine-awareness.md)).

## Callback

Apply custom filter logic with a closure.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(
        AllowedFilter::callback('search', function (Builder $query, mixed $value): void {
            $query->where('title', $value)->orWhere('body', $value);
        }),
    );
```

## Custom Filter Class

Implement the `Filter` interface for reusable filter logic.

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

These modifiers can be chained on any `AllowedFilter`:

```php
AllowedFilter::exact('status')
    ->default('published')    // applied when the filter is absent from the request
    ->nullable()              // allow null to pass through (skipped by default)
    ->ignore('draft', 'spam') // silently skip these values
    ->delimiter('|')          // override the multi-value delimiter (default: ,)
```
