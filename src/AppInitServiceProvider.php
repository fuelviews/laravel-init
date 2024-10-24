<?php

namespace Fuelviews\AppInit;

use Fuelviews\AppInit\Commands\ConfigureEnvCommand;
use Fuelviews\AppInit\Commands\InstallChangeLogCommand;
use Fuelviews\AppInit\Commands\InstallCommand;
use Fuelviews\AppInit\Commands\InstallComposerPackagesCommand;
use Fuelviews\AppInit\Commands\InstallGitDotFilesCommand;
use Fuelviews\AppInit\Commands\InstallPrettierCommand;
use Fuelviews\AppInit\Commands\InstallTailwindCssCommand;
use Fuelviews\AppInit\Commands\InstallViteCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AppInitServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('app-init')
            ->hasCommands([
                InstallCommand::class,
                InstallChangeLogCommand::class,
                InstallGitDotFilesCommand::class,
                InstallPrettierCommand::class,
                InstallTailwindCssCommand::class,
                InstallViteCommand::class,
                InstallComposerPackagesCommand::class,
                ConfigureEnvCommand::class,
            ]);
    }
}
