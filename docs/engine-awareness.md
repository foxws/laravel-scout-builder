# Engine Awareness

Scout supports multiple search engine drivers. Not all drivers support every Scout feature — for example, `whereNotIn()` or comparison operators may not be implemented in all engines.

Engine awareness lets you guard against using features your configured driver does not support.

## Enabling Enforcement

By default enforcement is **on when `APP_DEBUG` is `true`**. Override it explicitly in the config:

```php
// config/scout-builder.php
'engine_awareness' => [
    'enforce_support' => env('APP_DEBUG', false),
    ...
],
```

Or in a test / runtime context:

```php
config()->set('scout-builder.engine_awareness.enforce_support', true);
```

When enforcement is enabled and a filter or sort is applied that the active driver does not support, an `UnsupportedEngineFeature` exception is thrown.

## Configuring Allowed Drivers per Feature

Two features are guarded:

| Config key                | Applies to                                                               |
| ------------------------- | ------------------------------------------------------------------------ |
| `operator_filter_drivers` | `AllowedFilter::operator()` and `AllowedFilter::dynamicOperator()`       |
| `field_sort_drivers`      | `AllowedSort::field()`, `AllowedSort::latest()`, `AllowedSort::oldest()` |

By default all known drivers are in both lists. To restrict operator filters to only the database driver:

```php
'engine_awareness' => [
    'enforce_support' => true,
    'operator_filter_drivers' => ['database', 'collection'],
    'field_sort_drivers' => ['database', 'collection', 'algolia', 'meilisearch', 'typesense'],
],
```

## `ScoutDriver` Enum

All known driver identifiers are available as a typed enum:

```php
use Foxws\ScoutBuilder\Enums\ScoutDriver;

ScoutDriver::Database->value;    // 'database'
ScoutDriver::Meilisearch->value; // 'meilisearch'
ScoutDriver::values();           // ['database', 'collection', 'algolia', ...]
```

Cases: `Database`, `Collection`, `Algolia`, `Algolia3`, `Algolia4`, `Meilisearch`, `Typesense`, `Null`.

## `EngineFeature` Enum

```php
use Foxws\ScoutBuilder\Enums\EngineFeature;

EngineFeature::OperatorFilter // guards operator() / dynamicOperator()
EngineFeature::FieldSort      // guards field() / latest() / oldest()
```

## Manual Checks

You can call `EngineAwareness::ensureFeatureSupport()` directly for custom filters or sorts:

```php
use Foxws\ScoutBuilder\Enums\EngineFeature;
use Foxws\ScoutBuilder\Enums\ScoutDriver;
use Foxws\ScoutBuilder\Support\EngineAwareness;

EngineAwareness::ensureFeatureSupport(
    EngineFeature::OperatorFilter,
    [ScoutDriver::Database, ScoutDriver::Meilisearch],
);
```

Pass an array of `ScoutDriver` enum cases or plain driver strings as the allowed list.
