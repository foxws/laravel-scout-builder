---
section: Usage
order: 3
---

# Sorts

Sorts come from the `sort` parameter on the request. Prefix a name with `-`
to sort descending, and combine multiple sorts with commas. Like filters,
only sort names you've explicitly allowed are accepted — anything else
throws an `InvalidSortQuery` exception.

Examples:

| Request | Result |
|---|---|
| `?sort=created_at` | Ascending |
| `?sort=-created_at` | Descending |
| `?sort=-created_at,title` | Descending by `created_at`, then ascending by `title` |

## Field

Sorts by a column, using Scout's `orderBy()`.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedSorts('title', 'created_at');
```

Or use the explicit factory method:

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedSorts(AllowedSort::field('created_at'));
```

## Latest / Oldest

Sort by a timestamp column, under a friendlier public name.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedSorts(
        AllowedSort::latest('recent', 'published_at'),
        AllowedSort::oldest('chronological', 'published_at'),
    );
```

`?sort=recent` becomes `orderBy('published_at', 'desc')`.
`?sort=chronological` becomes `orderBy('published_at', 'asc')`.

Prefixing either with `-` flips the direction.

## Default Sort

Used only when the request has no `sort` parameter at all.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedSorts(AllowedSort::field('title'), AllowedSort::field('created_at'))
    ->defaultSort('-created_at');
```

For more than one default sort:

```php
->defaultSorts('-created_at', 'title')
```

## Descending Default

Make a sort descending by default, without the client needing to add `-`:

```php
AllowedSort::field('created_at')->defaultDescending()
```

## Callback

Write your own sort logic inline, with a closure.

```php
ScoutBuilder::for(Post::class, $request)
    ->allowedSorts(
        AllowedSort::callback('relevance', function (Builder $query, bool $descending, string $property): void {
            $query->orderBy('score', $descending ? 'desc' : 'asc');
        }),
    );
```

## Custom Sort Class

For sort logic you want to reuse, implement the `Sort` interface instead of
writing a closure.

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
