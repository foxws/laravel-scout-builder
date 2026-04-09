<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Support;

use Foxws\ScoutBuilder\Enums\EngineFeature;
use Foxws\ScoutBuilder\Exceptions\UnsupportedEngineFeature;
use Illuminate\Support\Facades\Config;

class EngineAwareness
{
    public static function ensureFeatureSupport(EngineFeature|string $featureKey): void
    {
        if (! Config::boolean('scout-builder.engine_awareness.enforce_support', false)) {
            return;
        }

        $featureKey = $featureKey instanceof EngineFeature ? $featureKey->value : $featureKey;
        $driver = (string) (Config::get('scout.driver') ?? 'null');

        $allowedDrivers = (array) Config::get("scout-builder.engine_awareness.{$featureKey}_drivers", []);

        if (! in_array($driver, $allowedDrivers, true)) {
            throw UnsupportedEngineFeature::featureNotSupported($featureKey, $driver, $allowedDrivers);
        }
    }
}
