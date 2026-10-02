---
title: Introduction
metadata:
  role: Search
  group: search
  eyebrow: "Laravel Scout · Query Builder · API Filters"
  desc: "Build safe Laravel Scout search queries straight from API requests."
  lead: "Let API clients filter, sort and paginate Laravel Scout results with query strings, in the style of spatie/laravel-query-builder."
  requires: "PHP ^8.4"
  laravel: "12.x / 13.x"
  licence: MIT
  used_by:
    - name: Stry
      desc: "A self-hosted video streaming app."
      href: "https://github.com/francoism90/stry"
    - name: foxws.nl
      desc: "This site."
      href: "https://foxws.nl"
---

# Introduction

[Laravel Scout](https://laravel.com/docs/scout) is Laravel's search package. This package adds a query builder on top of it: API clients can filter, sort, include relationships and paginate search results with query string parameters, and you don't write that logic by hand for every endpoint.

```php
use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;

$posts = ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(
        AllowedFilter::exact('status'),
        AllowedFilter::in('tags'),
    )
    ->allowedSorts(AllowedSort::field('title'))
    ->get();
```

This endpoint now answers requests like:

```text
/posts?query=laravel&filter[status]=published&filter[tags]=php,laravel&sort=title
```

Only the filters and sorts you allow are accepted, so clients can't query fields you didn't mean to expose.

## Features

- Exact, multi-value, operator (`?filter[price]=gte:100`), scope and custom filters.
- Sorting on fields, with a default sort.
- Including relationships in the results.
- Pagination, including JSON:API style `?page[number]=2&page[size]=15`.
- Wrap an existing Scout query and keep its own conditions.

## Installation

```bash
composer require foxws/laravel-scout-builder
```

## Credits

If you know [spatie/laravel-query-builder](https://github.com/spatie/laravel-query-builder), this will feel familiar: it's a close adaptation of it, built for Scout instead of Eloquent. All credit for the original design goes to [Spatie](https://spatie.be). If this package is useful to you, please consider [supporting Spatie](https://spatie.be/open-source/support-us).

## Learn more

- [Usage](usage.md)
- [Filters](filters.md), [Sorts](sorts.md), [Includes](includes.md) and [Pagination](pagination.md)
- [Engine awareness](engine-awareness.md): what each Scout engine supports.
- [Configuration](configuration.md)
