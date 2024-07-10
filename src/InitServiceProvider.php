<?php

namespace Fuelviews\Init;

use Fuelviews\Init\Commands\InitCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InitServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('init')
            ->hasCommand(InitCommand::class);
    }
}
