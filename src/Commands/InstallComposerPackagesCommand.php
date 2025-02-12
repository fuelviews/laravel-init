<?php

namespace Fuelviews\Init\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class InstallComposerPackagesCommand extends Command
{
    protected $signature = 'init:composer-packages {--force : Overwrite any existing files}';

    protected $description = 'Install Composer packages for Fuelviews and Laravel projects';

    public function handle(): void
    {
        $this->installComposerPackages();
        $this->info('Composer packages installed successfully.');
    }

    /**
     * Install Composer packages and run necessary commands
     */
    private function installComposerPackages(): void
    {
        $packages = [
            'fuelviews/laravel-sabhero-wrapper:">=0.0"',
            'fuelviews/laravel-cloudflare-cache:">=0.0"',
            'fuelviews/laravel-robots-txt:">=0.0"',
            'fuelviews/laravel-sitemap:">=0.0"',
            'ralphjsmit/laravel-seo:">=0.0"',
            'ralphjsmit/laravel-glide:">=0.0"',
            'livewire/livewire:">=0.0"',
            'spatie/laravel-google-fonts:">=0.0"',
        ];

        $packagesDev = [
            'spatie/image-optimizer:">=0.0"',
        ];

        // Install Composer packages
        $requireCommand = 'composer require';
        foreach ($packages as $package) {
            $requireCommand .= " {$package}";
        }

        // Install Composer packages
        $requireCommandDev = 'composer require --dev';
        foreach ($packagesDev as $package) {
            $requireCommandDev .= " {$package}";
        }

        $this->info('Installing Composer packages...');
        $this->runShellCommand($requireCommand);
        $this->runShellCommand($requireCommandDev);

        // Run package-specific install commands
        $this->runPackageInstallCommands();

        $filePath = base_path('routes/web.php');
        $search = "Route::get('/', function () {\n    return view('welcome');\n});";
        $replace = "Route::get('/', function () {\n    return view('welcome');\n})->name('welcome');";

        $fileContents = File::get($filePath);

        $updatedContents = str_replace($search, $replace, $fileContents);

        File::put($filePath, $updatedContents);
    }

    /**
     * Run specific installation commands for the packages
     */
    private function runPackageInstallCommands(): void
    {
        $force = $this->option('force') ? '--force' : '';

        $this->runShellCommand("php artisan vendor:publish --tag=cloudflare-cache-config {$force}");
        $this->runShellCommand("php artisan vendor:publish --tag=sitemap-config {$force}");
        $this->runShellCommand("php artisan vendor:publish --tag=robots-txt-config {$force}");
        $this->runShellCommand("php artisan vendor:publish --tag=laravel-sabhero-wrapper-seeders {$force}");
        $this->runShellCommand("php artisan vendor:publish --tag=laravel-sabhero-wrapper-welcome {$force}");
        $this->runShellCommand("php artisan vendor:publish --tag=seo-migrations {$force}");
        $this->runShellCommand("php artisan vendor:publish --tag=seo-config {$force}");

        $this->runShellCommand("php artisan vendor:publish --tag=google-fonts-config {$force}");

        $this->runShellCommand('php artisan sabhero-wrapper:install');

        if (! $this->isStorageLinked()) {
            $this->runShellCommand('php artisan storage:link');
        }
    }

    /**
     * Run shell commands in the system
     */
    private function runShellCommand($command): void
    {
        $this->info("Executing command: {$command}");

        $process = Process::fromShellCommandline($command);

        $process->setTty(Process::isTtySupported());

        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            $this->error("Command failed: {$command}");

            throw new ProcessFailedException($process);
        }
    }

    private function isStorageLinked(): bool
    {
        $publicStorageLink = public_path('storage');
        $storagePath = storage_path('app/public');

        return is_link($publicStorageLink) && readlink($publicStorageLink) === $storagePath;
    }
}
