<?php

namespace Fuelviews\LaravelInit;

use Fuelviews\LaravelInit\Commands\LaravelInitCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelInitServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-init')
            ->hasCommand(LaravelInitCommand::class);
    }
}
