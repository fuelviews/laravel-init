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
use Illuminate\Foundation\Console\AboutCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InitServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('init')
            ->hasAssets()
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

    public function packageRegistered(): void
    {
        $this->app->singleton(Init::class, function () {
            return new Init();
        });
    }

    public function packageBooted(): void
    {
        $this->publishes([
            __DIR__.'/../stubs/vite.config.js.stub' => base_path('vite.config.js'),
            __DIR__.'/../stubs/tailwind.config.js.stub' => base_path('tailwind.config.js'),
            __DIR__.'/../stubs/postcss.config.js.stub' => base_path('postcss.config.js'),
            __DIR__.'/../stubs/css/app.css.stub' => resource_path('css/app.css'),
        ], 'init-stubs');

        AboutCommand::add('Laravel Init', fn () => [
            'Version' => $this->getPackageVersion(),
            'Commands' => count($this->package->commands) . ' installation commands',
            'Stub Files' => 'Vite, Tailwind, PostCSS, and CSS configurations',
            'Assets' => 'Frontend configuration stubs available for publishing',
        ]);
    }

    private function getPackageVersion(): string
    {
        $composerFile = __DIR__.'/../composer.json';
        if (file_exists($composerFile)) {
            $composer = json_decode(file_get_contents($composerFile), true);
            return $composer['version'] ?? 'dev-main';
        }

        return 'unknown';
    }
}
