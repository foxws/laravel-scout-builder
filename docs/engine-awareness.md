---
section: Reference
order: 2
---

# Engine Awareness

Scout supports several search engine drivers, but not every driver supports
every Scout feature. For example, `whereNotIn()` or comparison operators
don't work the same way on every engine.

Engine awareness lets you guard against using a feature your configured
driver doesn't actually support.

## Enabling Enforcement

By default, enforcement is **on whenever `APP_DEBUG` is `true`**. You can
set it explicitly instead, in the config:

```php
// config/scout-builder.php
'engine_awareness' => [
    'enforce_support' => env('APP_DEBUG', false),
    ...
],
```

Or turn it on at runtime, e.g. in a test:

```php
config()->set('scout-builder.engine_awareness.enforce_support', true);
```

When enforcement is on and you apply a filter or sort that your active
driver doesn't support, it throws an `UnsupportedEngineFeature` exception.

## Configuring Allowed Drivers per Feature

Two features are guarded:

| Config key | Applies to |
|---|---|
| `operator_filter_drivers` | `AllowedFilter::operator()` and `AllowedFilter::dynamicOperator()` |
| `field_sort_drivers` | `AllowedSort::field()`, `AllowedSort::latest()`, `AllowedSort::oldest()` |

By default, every known driver is allowed for both. To restrict operator
filters to only the database driver, for example:

```php
'engine_awareness' => [
    'enforce_support' => true,
    'operator_filter_drivers' => ['database', 'collection'],
    'field_sort_drivers' => ['database', 'collection', 'algolia', 'meilisearch', 'typesense'],
],
```

## `ScoutDriver` Enum

Every known driver identifier is available as a typed enum:

```php
use Foxws\ScoutBuilder\Enums\ScoutDriver;

ScoutDriver::Database->value;    // 'database'
ScoutDriver::Meilisearch->value; // 'meilisearch'
ScoutDriver::values();           // ['database', 'collection', 'algolia', ...]
```

Cases: `Database`, `Collection`, `Algolia`, `Algolia3`, `Algolia4`,
`Meilisearch`, `Typesense`, `Null`.

## `EngineFeature` Enum

```php
use Foxws\ScoutBuilder\Enums\EngineFeature;

EngineFeature::OperatorFilter // guards operator() / dynamicOperator()
EngineFeature::FieldSort      // guards field() / latest() / oldest()
```

## Manual Checks

You can call `EngineAwareness::ensureFeatureSupport()` directly, for your
own custom filters or sorts:

```php
use Foxws\ScoutBuilder\Enums\EngineFeature;
use Foxws\ScoutBuilder\Enums\ScoutDriver;
use Foxws\ScoutBuilder\Support\EngineAwareness;

EngineAwareness::ensureFeatureSupport(
    EngineFeature::OperatorFilter,
    [ScoutDriver::Database, ScoutDriver::Meilisearch],
);
```

Pass it an array of `ScoutDriver` enum cases or plain driver strings as the
allowed list.
