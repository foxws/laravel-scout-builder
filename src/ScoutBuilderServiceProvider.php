<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder;

use Foxws\ScoutBuilder\Commands\ScoutBuilderCommand;
use Illuminate\Http\Request;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ScoutBuilderServiceProvider extends PackageServiceProvider
{
    public function registeringPackage(): void
    {
        $this->app->bind(QueryBuilderRequest::class, function (): QueryBuilderRequest {
            $request = $this->app->make(Request::class);

            return QueryBuilderRequest::fromRequest($request);
        });

        $this->app->singleton(ScoutBuilder::class, fn (): ScoutBuilder => new ScoutBuilder);
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-scout-builder')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_scout_builder_table')
            ->hasCommand(ScoutBuilderCommand::class);
    }
}
