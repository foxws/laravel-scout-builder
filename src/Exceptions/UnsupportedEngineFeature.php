<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Exceptions;

final class UnsupportedEngineFeature extends InvalidQuery
{
    /**
     * @param  list<string>  $supportedDrivers
     */
    public static function featureNotSupported(string $feature, string $driver, array $supportedDrivers): static
    {
        $supportedDriverList = implode(', ', $supportedDrivers);

        return new self("Feature `{$feature}` is not configured for Scout driver `{$driver}`. Supported drivers: `{$supportedDriverList}`.");
    }
}
