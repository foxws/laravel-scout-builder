<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder;

use Illuminate\Http\Request;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ScoutBuilderServiceProvider extends PackageServiceProvider
{
    public function registeringPackage(): void
    {
        $this->app->bind(ScoutBuilderRequest::class, function (): ScoutBuilderRequest {
            $request = $this->app->make(Request::class);

            return ScoutBuilderRequest::fromRequest($request);
        });

        $this->app->singleton(ScoutBuilderFactory::class, fn (): ScoutBuilderFactory => new ScoutBuilderFactory);
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('scout-builder')
            ->hasConfigFile();
    }
}
