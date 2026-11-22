---
section: Usage
order: 2
---

# Filters

Filters come from the `filter` parameter on the request. You have to
explicitly allow each filter you want to accept — any filter name that
isn't allowed throws an `InvalidFilterQuery` exception.

## Validation

Allowing a filter doesn't validate its value, and search engines such as
Typesense have filter syntax that Scout can't always quote. Validate the values
before you search, with `Rule::in()` or a strict pattern rather than `string`
alone. The value of an `in` or `notIn` filter is the whole comma-separated string:

```php
use Illuminate\Validation\Rule;

$request->validate([
    'filter.status' => ['string', Rule::in(['draft', 'published'])],
    'filter.tags' => ['string', 'regex:/^[a-z0-9,-]+$/'],
]);
```

The same rules fit in a `FormRequest`. For a rule that belongs to a reusable
filter, you can also [validate in a custom filter](#validating-in-a-custom-filter).

## Exact

Matches a field exactly, using Scout's `where()`.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::exact('status'));
```

Request: `?filter[status]=published`

It takes a single value: `?filter[status]=draft,published` throws
`InvalidFilterValue`. Use `in` to accept several, or `->delimiter('')` when the
values themselves contain commas.

A value containing a nested array, like `?filter[status][0][x]=1`, is ignored
and no constraint is added.

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

Both can only narrow a constraint that's already on the builder. After
`->whereIn('team_id', [1, 2])`, `?filter[team_id]=2,3` searches team 2 only, and
`notIn` adds to an existing `whereNotIn()`. Callback and custom filters that call
`whereIn()` or `whereNotIn()` replace the existing constraint, as Scout does.

`in` and `notIn` ignore nested arrays in their values while keeping valid
siblings: `?filter[tags][0][x]=1&filter[tags][1]=php` filters on `php`. When no
usable values remain, no constraint is added.

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

Like `exact`, operator filters take a single value.

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

A string whose part before the colon isn't a known token is compared with
`=` as a whole, so values such as `https://example.com` or `10:30` work
without the array payload. An unrecognised operator in the array payload
throws `InvalidFilterValue`.

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

### Validating in a custom filter

A custom filter can validate its own value, so the rule applies wherever you use
the filter. An invalid value gives the usual `422` response:

```php
use Foxws\ScoutBuilder\Filters\Filter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Scout\Builder;

class FiltersStatus implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        Validator::make(
            ['filter' => [$property => $value]],
            ["filter.{$property}" => ['required', 'string', Rule::in(['draft', 'published'])]],
        )->validate();

        $query->where($property, $value);
    }
}

// In the allowedFilters() call:
AllowedFilter::custom('status', new FiltersStatus)->nullable();
```

It gets the value after the builder handled it, which differs from a `FormRequest`:

- **Skipped values aren't validated.** Without `->nullable()`, an empty value and
  `null` never reach the filter, which also drops its default. Ignored values
  never reach it either.
- **Values are cast.** `42` is an integer and `true` a boolean, so use rules like
  `integer` and `boolean` instead of `string`. `007` has become `7`.
- **Values are split.** `a,b` is an array, so use `array` with a rule per item for
  several values, or `->delimiter('')` to keep the string.
- **Keep `required`.** Laravel skips rules such as `Rule::in()` for an empty value.

Validate in a `FormRequest` when you need the value exactly as it was sent.

## Modifiers

You can chain these onto any `AllowedFilter` to fine-tune its behaviour:

| Modifier | What it does |
|---|---|
| `->default(...)` | Value used when the filter is missing from the request |
| `->nullable()` | Allow `null` through (skipped by default) |
| `->ignore(...)` | Remove specific values; the filter is skipped when nothing is left |
| `->delimiter(...)` | Change the multi-value separator (default `,`) |

```php
AllowedFilter::exact('status')
    ->default('published')    // applied when the filter is absent from the request
    ->nullable()              // allow null to pass through (skipped by default)
    ->ignore('draft', 'spam') // silently skip these values
    ->delimiter('|')          // override the multi-value delimiter (default: ,)
```

The filter values `true` and `false` are only ignored by `ignore(true)`/`ignore('true')`
or `ignore(false)`/`ignore('false')`, so ignoring `null` or `''` on a boolean filter
does not drop `filter[active]=false`. This also applies to booleans in arrays and
defaults. Other values still compare loosely, so `ignore(6)` also ignores `'6'`.
