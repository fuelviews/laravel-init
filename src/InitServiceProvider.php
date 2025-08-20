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
use Fuelviews\Init\Commands\StatusCommand;
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
                StatusCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('init', function () {
            return new Init();
        });
    }

    public function packageBooted(): void
    {
        $this->registerPublishables();
        $this->registerAboutCommand();
    }

    protected function registerPublishables(): void
    {
        // All stubs at once
        $this->publishes([
            __DIR__.'/../stubs/vite.config.js.stub' => base_path('vite.config.js'),
            __DIR__.'/../stubs/tailwind.config.js.stub' => base_path('tailwind.config.js'),
            __DIR__.'/../stubs/postcss.config.js.stub' => base_path('postcss.config.js'),
            __DIR__.'/../stubs/css/app.css.stub' => resource_path('css/app.css'),
        ], ['init-stubs', 'init-all']);

        // Vite specific
        $this->publishes([
            __DIR__.'/../stubs/vite.config.js.stub' => base_path('vite.config.js'),
        ], 'init-vite');

        // Tailwind specific (includes PostCSS as it's required)
        $this->publishes([
            __DIR__.'/../stubs/tailwind.config.js.stub' => base_path('tailwind.config.js'),
            __DIR__.'/../stubs/postcss.config.js.stub' => base_path('postcss.config.js'),
        ], 'init-tailwind');

        // Styles
        $this->publishes([
            __DIR__.'/../stubs/css/app.css.stub' => resource_path('css/app.css'),
        ], 'init-styles');
    }

    protected function registerAboutCommand(): void
    {
        AboutCommand::add('Laravel Init', fn () => [
            'Version' => $this->getPackageVersion(),
            'Commands' => count($this->package->commands) . ' commands',
            'Stub Files' => $this->countStubFiles() . ' configuration stubs',
            'Publishing Tags' => 'init-all, init-stubs, init-vite, init-tailwind, init-styles',
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

    private function countStubFiles(): int
    {
        $stubPath = __DIR__.'/../stubs';
        if (! is_dir($stubPath)) {
            return 0;
        }

        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($stubPath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'stub') {
                $count++;
            }
        }

        return $count;
    }
}
