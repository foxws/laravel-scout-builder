# laravel-scout-builder

[![Latest Version on Packagist](https://img.shields.io/packagist/v/foxws/laravel-scout-builder.svg?style=flat-square)](https://packagist.org/packages/foxws/laravel-scout-builder)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/foxws/laravel-scout-builder/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/foxws/laravel-scout-builder/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/foxws/laravel-scout-builder/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/foxws/laravel-scout-builder/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/foxws/laravel-scout-builder.svg?style=flat-square)](https://packagist.org/packages/foxws/laravel-scout-builder)

A [Laravel Scout](https://laravel.com/docs/scout) query builder inspired by and largely derived from [spatie/laravel-query-builder](https://github.com/spatie/laravel-query-builder). It brings the same `AllowedFilter` / `AllowedSort` API to Scout's search builder, letting you expose safe, declarative search endpoints driven by HTTP query parameters.

> **Credits** — This package is a close adaptation of spatie/laravel-query-builder. All credit for the original architecture, patterns, and API design belongs to [Spatie](https://spatie.be). If this package is useful to you, please consider [supporting Spatie](https://spatie.be/open-source/support-us).

## Documentation

- [Filters](docs/filters.md)
- [Sorts](docs/sorts.md)
- [Includes](docs/includes.md)
- [Pagination](docs/pagination.md)
- [Engine Awareness](docs/engine-awareness.md)
- [Configuration](docs/configuration.md)

## Installation

```bash
composer require foxws/laravel-scout-builder
```

Publish the config file (optional):

```bash
php artisan vendor:publish --tag="scout-builder-config"
```

## Quick Start

Add the `Searchable` trait to your model as usual, then build a search endpoint:

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

| Parameter | Example |
|---|---|
| Search query | `?query=laravel` |
| Exact filter | `?filter[status]=published` |
| Multi-value filter | `?filter[tags]=php,laravel` |
| Operator filter | `?filter[price]=gte:100` |
| Sort | `?sort=-recent,title` |
| Paginate | `?page[number]=2&page[size]=15` |

## Pagination

Use `jsonPaginate()` instead of `get()` to return a paginated result following the JSON:API `page[number]` / `page[size]` convention:

```php
$results = ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(AllowedFilter::exact('status'))
    ->allowedSorts(AllowedSort::field('title'))
    ->jsonPaginate();
```

| Parameter | Example | Default |
|---|---|---|
| Page number | `?page[number]=2` | `1` |
| Page size | `?page[size]=15` | `30` |

The max page size is capped at `30` by default. Both defaults are configurable — see [Pagination](docs/pagination.md).

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

| Feature | spatie/laravel-query-builder | foxws/laravel-scout-builder |
|---|---|---|
| Underlying builder | Eloquent `Builder` | Scout `Builder` |
| `AllowedInclude` | ✅ | ✅ via Scout `query()` callback (database/collection drivers) |
| `FiltersPartial`, `FiltersBeginsWith`, etc. | ✅ | — (text search handled by Scout itself) |
| `AllowedFilter::operator()` | via `FiltersOperator` | ✅ first-class with `FilterOperator` enum |
| `AllowedFilter::dynamicOperator()` | — | ✅ colon-token + array payload |
| `AllowedFilter::notIn()` | — | ✅ |
| `AllowedSort::latest()` / `oldest()` | — | ✅ |
| `jsonPaginate()` | ✅ (Eloquent only) | ✅ JSON:API `page[number]`/`page[size]` |
| Engine awareness | — | ✅ `ScoutDriver` + `EngineFeature` enums |
| Request scalar casting | raw strings | ✅ auto-casts `'true'`, `'42'`, `'null'`, etc. |

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## AI Assistance

This package is developed with AI assistance, primarily using [GitHub Copilot](https://github.com/features/copilot) and [Claude Sonnet](https://www.anthropic.com/claude).

AI tools are used for suggestions and development acceleration. All final implementation decisions, code review, and adjustments are made by the maintainers. AI-generated contributions are welcome in pull requests, provided that a person is actively involved in the implementation and review process.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Spatie](https://spatie.be) and [all spatie/laravel-query-builder contributors](https://github.com/spatie/laravel-query-builder/graphs/contributors) — this package is built on their work
- [francoism90](https://github.com/foxws)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
