<?php

namespace Fuelviews\Init;

use Fuelviews\Init\Commands\LaravelInitCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelInitServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('init')
            ->hasCommand(LaravelInitCommand::class);
    }
}
