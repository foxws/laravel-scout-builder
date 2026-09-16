---
section: Usage
order: 4
---

# Includes

Includes let you load relationships or aggregate counts through Scout's
`query()` callback, which hands you a full Eloquent builder. Because of
this, includes run during the Eloquent hydration step — after the search
engine has already found the matching IDs.

> **Driver note:** Only drivers that use Eloquent to hydrate results
> (`database`, `collection`) run include callbacks. Remote engines (Algolia,
> Typesense, Meilisearch) do their own search and never call the
> `queryCallback`, so includes are silently ignored on those drivers.

Includes come from the `include` request parameter, as a comma-separated
list.

## Relationship

Eager-loads a relationship with `with()`.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedIncludes(
        AllowedInclude::relationship('author'),
        AllowedInclude::relationship('comments'),
    );
```

Request: `?include=author,comments`

A plain string is shorthand for `relationship()`:

```php
->allowedIncludes('author', 'comments')
```

## Count

Loads a relationship count with `withCount()`.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedIncludes(AllowedInclude::count('comments'));
```

Request: `?include=comments`

Each model gets a `comments_count` attribute in the result.

## Internal Name

Give an include a public name that differs from the underlying relationship
or column:

```php
AllowedInclude::relationship('writer', 'author')   // ?include=writer → ->with('author')
AllowedInclude::count('numComments', 'comments')   // ?include=numComments → ->withCount('comments')
```

## Callback

Write your own include logic inline, with a closure:

```php
AllowedInclude::callback('latestComments', function (Builder $query, string $include): void {
    $query->query(function (EloquentBuilder $builder) {
        $builder->with(['comments' => fn ($q) => $q->latest()->limit(5)]);
    });
});
```

## Custom Include Class

For include logic you want to reuse, implement the `Includable` interface
instead of writing a closure:

```php
use Foxws\ScoutBuilder\Includes\Includable;
use Laravel\Scout\Builder;

class IncludesLatestComments implements Includable
{
    public function __invoke(Builder $query, string $include): void
    {
        $existing = $query->queryCallback;

        $query->query(function ($builder) use ($existing): void {
            if ($existing !== null) {
                ($existing)($builder);
            }

            $builder->with(['comments' => fn ($q) => $q->latest()->limit(5)]);
        });
    }
}

ScoutBuilder::for(Post::class, $request)
    ->allowedIncludes(AllowedInclude::custom('latestComments', new IncludesLatestComments));
```

## Chaining

You can combine multiple includes safely. Each one wraps the previous
`queryCallback`, so none of them overwrite each other.

## Disabling the Exception

By default, an unknown include name throws `InvalidIncludeQuery`. To ignore
unknown names instead of throwing:

```php
// config/scout-builder.php
'disable_invalid_include_query_exception' => true,
```
