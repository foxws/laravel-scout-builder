<?php

namespace Foxws\ScoutBuilder;

use Foxws\ScoutBuilder\Commands\ScoutBuilderCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ScoutBuilderServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-scout-builder')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_scout_builder_table')
            ->hasCommand(ScoutBuilderCommand::class);
    }
}
