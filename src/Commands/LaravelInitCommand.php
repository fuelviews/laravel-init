<?php

namespace Fuelviews\LaravelInit\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class LaravelInitCommand extends Command
{
    protected $signature = 'laravel-init:install {--force : Overwrite any existing files}';

    protected $description = 'Install all Fuelviews packages and run their install commands';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $packages = [
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
        $this->runShellCommand("php artisan vendor:publish --tag=navigation-config {$force}");
        $this->runShellCommand("php artisan vendor:publish --tag=forms-config {$force}");

        $this->runShellCommand('php artisan vite:install');
        $this->runShellCommand('php artisan tailwindcss:install');
        $this->runShellCommand('php artisan deploy:install');
        $this->runShellCommand('php artisan navigation:install');
        $this->runShellCommand('php artisan forms:install');

        $this->info('Packages installed successfully.');
    }

    private function runShellCommand($command)
    {
        $process = Process::fromShellCommandline($command);

        // Set the input to the process's standard input, allowing for interaction
        $process->setTty(Process::isTtySupported());

        // Run the process
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
