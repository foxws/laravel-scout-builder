<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Support;

use Foxws\ScoutBuilder\Exceptions\UnsupportedEngineFeature;
use Illuminate\Support\Facades\Config;

class EngineAwareness
{
    /**
     * @param  list<string>  $fallbackDrivers
     */
    public static function ensureFeatureSupport(string $featureKey, array $fallbackDrivers): void
    {
        if (! Config::boolean('scout-builder.engine_awareness.enforce_support', false)) {
            return;
        }

        $driver = (string) Config::get('scout.driver', '');

        $configuredDrivers = Config::get("scout-builder.engine_awareness.{$featureKey}_drivers", $fallbackDrivers);
        $allowedDrivers = is_array($configuredDrivers) ? $configuredDrivers : $fallbackDrivers;

        if (! in_array($driver, $allowedDrivers, true)) {
            throw UnsupportedEngineFeature::featureNotSupported($featureKey, $driver, $allowedDrivers);
        }
    }
}
