<?php

namespace Fuelviews\Init;

use Fuelviews\Init\Commands\ConfigureEnvCommand;
use Fuelviews\Init\Commands\InstallChangeLogCommand;
use Fuelviews\Init\Commands\InstallCommand;
use Fuelviews\Init\Commands\InstallComposerPackagesCommand;
use Fuelviews\Init\Commands\InstallGitDotFilesCommand;
use Fuelviews\Init\Commands\InstallPrettierCommand;
use Fuelviews\Init\Commands\InstallTailwindCssCommand;
use Fuelviews\Init\Commands\InstallViteCommand;
use Fuelviews\Init\Commands\ProcessImagesCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InitServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('init')
            ->hasCommands([
                InstallCommand::class,
                InstallChangeLogCommand::class,
                InstallGitDotFilesCommand::class,
                InstallPrettierCommand::class,
                InstallTailwindCssCommand::class,
                InstallViteCommand::class,
                InstallComposerPackagesCommand::class,
                ConfigureEnvCommand::class,
                ProcessImagesCommand::class,
            ]);

    }
}
