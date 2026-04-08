# Sorts

Sorts are applied from the HTTP request's `sort` parameter. Prefix a sort name with `-` to sort descending. Multiple sorts can be comma-separated. Only sort names that are explicitly allowed are accepted — unknown names throw an `InvalidSortQuery` exception.

Request examples:

- `?sort=created_at` — ascending
- `?sort=-created_at` — descending
- `?sort=-created_at,title` — multiple sorts

## Field

Applies a Scout `orderBy()`.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedSorts('title', 'created_at');
```

Or using the explicit static factory:

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedSorts(AllowedSort::field('created_at'));
```

## Latest / Oldest

Sort by a timestamp column using a friendly name.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedSorts(
        AllowedSort::latest('recent', 'published_at'),
        AllowedSort::oldest('chronological', 'published_at'),
    );
```

Request: `?sort=recent` maps to `orderBy('published_at', 'desc')`.
Request: `?sort=chronological` maps to `orderBy('published_at', 'asc')`.

When sorted in reverse (prefixed with `-`), the directions flip.

## Default Sort

Applied when no `sort` parameter is present in the request.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedSorts(AllowedSort::field('title'), AllowedSort::field('created_at'))
    ->defaultSort('-created_at');
```

Multiple defaults:

```php
->defaultSorts('-created_at', 'title')
```

## Descending Default

Mark a sort as descending by default when it is used:

```php
AllowedSort::field('created_at')->defaultDescending()
```

## Callback

Apply custom sort logic with a closure.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedSorts(
        AllowedSort::callback('relevance', function (Builder $query, bool $descending, string $property): void {
            $query->orderBy('score', $descending ? 'desc' : 'asc');
        }),
    );
```

## Custom Sort Class

Implement the `Sort` interface for reusable sort logic.

```php
use Foxws\ScoutBuilder\Sorts\Sort;
use Laravel\Scout\Builder;

class SortsByScore implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        $query->orderBy('score', $descending ? 'desc' : 'asc');
    }
}

ScoutBuilder::for(Post::class, $request)
    ->allowedSorts(AllowedSort::custom('score', new SortsByScore));
```
