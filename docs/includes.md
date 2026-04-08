# Includes

Includes load relationships or aggregates via Scout's `query()` callback, which gives you a full Eloquent builder. This means they apply during the Eloquent hydration step — after the search engine returns matching IDs.

> **Driver note:** Include callbacks are only executed by drivers that use Eloquent to hydrate results (`database`, `collection`). Remote engines (Algolia, Typesense, Meilisearch) run the search themselves and do not call the `queryCallback` — includes are silently ignored for those drivers.

Includes are driven by the `?include=` request parameter (comma-separated).

## Relationship

Eagerly loads a relationship via `with()`.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedIncludes(
        AllowedInclude::relationship('author'),
        AllowedInclude::relationship('comments'),
    );
```

Request: `?include=author,comments`

Passing a plain string is shorthand for `relationship()`:

```php
->allowedIncludes('author', 'comments')
```

## Count

Loads an aggregate count of a relationship via `withCount()`.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedIncludes(AllowedInclude::count('comments'));
```

Request: `?include=comments`

The result adds a `comments_count` attribute to each model.

## Internal Name

Use `$internalName` to decouple the public parameter name from the relationship or column name:

```php
AllowedInclude::relationship('writer', 'author')   // ?include=writer → ->with('author')
AllowedInclude::count('numComments', 'comments')   // ?include=numComments → ->withCount('comments')
```

## Callback

Custom include logic with a closure:

```php
AllowedInclude::callback('latestComments', function (Builder $query, string $include): void {
    $query->query(function (EloquentBuilder $builder) {
        $builder->with(['comments' => fn ($q) => $q->latest()->limit(5)]);
    });
});
```

## Custom Include Class

Implement the `Includable` interface for reusable include logic:

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

Multiple includes chain safely — each wraps the previous `queryCallback` so no include overwrites another.

## Disabling the Exception

Unknown include names throw `InvalidIncludeQuery` by default. To silently ignore them instead:

```php
// config/scout-builder.php
'disable_invalid_include_query_exception' => true,
```
