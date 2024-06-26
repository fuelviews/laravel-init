<?php

namespace Fuelviews\Init\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class LaravelInitCommand extends Command
{
    protected $signature = 'init:install {--force : Overwrite any existing files}';

    protected $description = 'Install all Fuelviews packages and run their install commands';

    public function handle()
    {
        $packages = [
            'fuelviews/laravel-layout-wrapper' => '^0.0',
            'fuelviews/laravel-cloudflare-cache' => '^0.0',
            'fuelviews/laravel-robots-txt' => '^0.0',
            'fuelviews/laravel-sitemap' => '^0.0',
            'fuelviews/laravel-tailwindcss' => '^0.0',
            'fuelviews/laravel-vite' => '^0.0',
            'fuelviews/laravel-cpanel-auto-deploy' => '^0.0',
            'fuelviews/laravel-navigation' => '^0.0',
            'fuelviews/laravel-forms' => '^0.0',
            'spatie/laravel-medialibrary' => '^11.0',
        ];

        $requireCommand = 'composer require';
        foreach ($packages as $package => $version) {
            $requireCommand .= " {$package}:{$version}";
        }

        $this->info('Installing packages...');
        $this->runShellCommand($requireCommand);

        $this->info('Running package-specific install commands...');

        $force = $this->option('force') ? '--force' : '';

        $this->runShellCommand("php artisan vendor:publish --tag=cloudflare-cache-config {$force}");
        $this->runShellCommand("php artisan vendor:publish --tag=sitemap-config {$force}");
        $this->runShellCommand("php artisan vendor:publish --tag=navigation-config {$force} && php artisan vendor:publish --tag=navigation-spacer {$force}");
        $this->runShellCommand("php artisan vendor:publish --tag=navigation-logo {$force}");
        $this->runShellCommand("php artisan vendor:publish --tag=forms-config {$force}");
        $this->runShellCommand("php artisan vendor:publish --provider='Spatie\MediaLibrary\MediaLibraryServiceProvider' --tag=medialibrary-migrations {$force}");

        $this->runShellCommand("php artisan vite:install {$force}");
        $this->runShellCommand("php artisan tailwindcss:install {$force}");
        $this->runShellCommand('php artisan layout-wrapper:install');
        $this->runShellCommand('php artisan navigation:install');
        $this->runShellCommand("php artisan forms:install {$force}");
        $this->runShellCommand('php artisan deploy:install || true');

        $this->runShellCommand('php artisan storage:link');
        $this->runShellCommand("php artisan migrate {$force}");

        $this->info('Packages installed successfully.');
    }

    private function runShellCommand($command)
    {
        $process = Process::fromShellCommandline($command);

        $process->setTty(Process::isTtySupported());

        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
