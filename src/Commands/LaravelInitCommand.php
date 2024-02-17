<?php

namespace Fuelviews\LaravelInit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\comment;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class LaravelInitCommand extends Command
{
    protected $signature = 'laravel-init';

    protected $description = 'Initialize Laravel project with custom settings';

    public function handle(): int
    {
        // Check if vite.config.js already exists
        if (File::exists(base_path('vite.config.js'))) {
            // Confirm overwrite if file exists
            if (confirm(
                label: 'Vite.config.js already exists. Do you want to overwrite it?',
                default: false
            )) {
                $this->publishViteConfig('overwrite');
            } else {
                $this->warn('Skipping vite.config.js installation.');
            }
        } else {
            // Confirm installation if file doesn't exist
            if (confirm(
                label: 'Vite.config.js does not exist. Would you like to install it now?',
                default: true,
            )) {
                $this->publishViteConfig('install');
            }
        }

        // Check if the Filament package is already installed
        $isFilamentInstalled = false;

        // Get the list of installed packages using Composer
        $composerJson = json_decode(file_get_contents(base_path('composer.json')), true);
        $installedPackages = array_keys($composerJson['require']);

        // Check if Filament is among the installed packages
        if (in_array('filament/filament', $installedPackages)) {
            $isFilamentInstalled = true;
        }

        if ($isFilamentInstalled) {
            if (confirm(
                label: 'FilamentPHP is installed. Do you want to overwrite Filament?',
                default: false
            )) {
                $this->installFilament();
            } else {
                $this->warn('Skipping FilamentPHP installation.');
            }
        } else {
            if (!$isFilamentInstalled && confirm(
                label: 'FilamentPHP is not installed. Do you want to install Filament?',
                default: true
            ));
            $this->installFilament();
            $this->info('FilamentPHP installed.');
        }

        $this->comment('All done');

        return self::SUCCESS;
    }

    protected function publishViteConfig($action)
    {
        $stubPath = __DIR__ . '/../../resources/stubs/vite.config.js';
        $destinationPath = base_path('vite.config.js');

        if ($action === 'overwrite') {
            $this->warn('Overwriting existing vite.config.js file.');
        }

        if (File::copy($stubPath, $destinationPath)) {
            $this->info('Vite.config.js has been ' . ($action === 'overwrite' ? 'overwritten' : 'published') . ' successfully.');
        } else {
            $this->error('Failed to publish vite.config.js.');
        }
    }

    protected function installFilament()
    {
        $this->info('Installing FilamentPHP...');

        // Run the composer require command to install Filament
        $composerProcess = Process::fromShellCommandline('composer require filament/filament:^3.2 -W', null, null, null, null);
        $composerProcess->run();

        // Check if the command failed
        if (!$composerProcess->isSuccessful()) {
            throw new ProcessFailedException($composerProcess);
        }

        $this->info($composerProcess->getOutput());

        // Run the composer require command to install Filament
        if (!file_exists(config_path('filament.php'))) {
            $composerProcess = Process::fromShellCommandline('php artisan vendor:publish --tag=filament-config', null, null, null, null);
            $composerProcess->run();
        } else {
            $this->info('Filament configuration file already exists.');
        }

        $this->info($composerProcess->getOutput());

    }
}
