<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Support;

use Foxws\ScoutBuilder\Enums\EngineFeature;
use Foxws\ScoutBuilder\Enums\ScoutDriver;
use Foxws\ScoutBuilder\Exceptions\UnsupportedEngineFeature;
use Illuminate\Support\Facades\Config;

class EngineAwareness
{
    /**
     * @param  list<ScoutDriver|string>  $fallbackDrivers
     */
    public static function ensureFeatureSupport(EngineFeature|string $featureKey, array $fallbackDrivers): void
    {
        if (! Config::boolean('scout-builder.engine_awareness.enforce_support', false)) {
            return;
        }

        $featureKey = $featureKey instanceof EngineFeature ? $featureKey->value : $featureKey;
        $driver = (string) Config::get('scout.driver', '');

        $configuredDrivers = Config::get("scout-builder.engine_awareness.{$featureKey}_drivers", $fallbackDrivers);

        $allowedDrivers = is_array($configuredDrivers) ? $configuredDrivers : $fallbackDrivers;
        $allowedDrivers = array_map(
            static fn (ScoutDriver|string $allowedDriver): string => $allowedDriver instanceof ScoutDriver ? $allowedDriver->value : $allowedDriver,
            $allowedDrivers,
        );

        if (! in_array($driver, $allowedDrivers, true)) {
            throw UnsupportedEngineFeature::featureNotSupported($featureKey, $driver, $allowedDrivers);
        }
    }
}
