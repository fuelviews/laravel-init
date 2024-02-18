<?php

namespace Fuelviews\LaravelInit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\confirm;

class LaravelInitCommand extends Command
{
    protected $signature = 'laravel-init';

    protected $description = 'Initialize Laravel project with custom settings';

    public function handle(): int
    {
        $this->publishConfig('vite.config.js');
        $this->publishConfig('tailwind.config.js');
        $this->publishConfig('postcss.config.js');

        $this->publishAppCss();

        $devDependenciesToCheck = [
            '@tailwindcss/forms',
            '@tailwindcss/typography',
            'autoprefixer',
            'postcss',
            'dotenv',
            'tailwindcss',
        ];

        $packageJsonPath = base_path('package.json');
        $packageJsonContent = File::get($packageJsonPath);
        $packageJson = json_decode($packageJsonContent, true);

        foreach ($devDependenciesToCheck as $packageName) {
            if (! isset($packageJson['devDependencies'][$packageName])) {
                if (confirm("{$packageName} is not installed. Do you want to install it now?", true)) {
                    $this->installNodePackage($packageName, true);
                }
            } else {
                $this->comment("{$packageName} is already installed.");
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
                label: 'FilamentPHP is installed. Do you want to overwrite FilamentPHP installation?',
                default: false
            )) {
                $this->installFilament();
            } else {
                $this->warn('Skipping FilamentPHP installation.');
            }
        } else {
            if (! $isFilamentInstalled && confirm(
                label: 'FilamentPHP is not installed. Do you want to install FilamentPHP?',
                default: true
            ));
            $this->installFilament();
            $this->info('FilamentPHP installed successfully.');
        }

        $this->info('All done');

        return self::SUCCESS;
    }

    protected function publishConfig($configFileName)
    {
        $stubPath = __DIR__."/../../resources/stubs/{$configFileName}";
        $destinationPath = base_path($configFileName);

        // Check if config file already exists
        if (File::exists($destinationPath)) {
            // Confirm overwrite if file exists
            if (confirm("{$configFileName} already exists. Do you want to overwrite it?", false)) {
                File::copy($stubPath, $destinationPath);
                $this->info("{$configFileName} has been overwritten successfully.");
            } else {
                $this->warn("Skipping {$configFileName} installation.");
            }
        } else {
            // Confirm installation if file doesn't exist
            if (confirm("{$configFileName} does not exist. Would you like to install it now?", true)) {
                File::copy($stubPath, $destinationPath);
                $this->info("{$configFileName} has been installed successfully.");
            }
        }
    }

    protected function publishAppCss()
    {
        $stubPath = __DIR__.'/../../resources/stubs/css/app.css';
        $destinationPath = resource_path('css/app.css');

        // Check if the directory exists, if not create it
        if (! File::exists(dirname($destinationPath))) {
            File::makeDirectory(dirname($destinationPath), 0755, true);
        }

        // Check if app.css already exists
        if (File::exists($destinationPath)) {
            // Confirm overwrite if file exists
            if ($this->confirm('The css/app.css file already exists. Do you want to overwrite it?', false)) {
                File::copy($stubPath, $destinationPath);
                $this->info('The css/app.css file has been overwritten successfully.');
            } else {
                $this->warn('Skipping css/app.css installation.');
            }
        } else {
            // Confirm installation if file doesn't exist
            if ($this->confirm('The css/app.css file does not exist. Would you like to install it now?', true)) {
                File::copy($stubPath, $destinationPath);
                $this->info('The css/app.css file has been installed successfully.');
            }
        }
    }

    protected function detectPackageManager(): string
    {
        if (File::exists(base_path('yarn.lock'))) {
            return 'yarn';
        } elseif (File::exists(base_path('package-lock.json'))) {
            return 'npm';
        } else {
            // Default to npm if unable to determine
            return 'npm';
        }
    }

    protected function installNodePackage($packageName, $isDev = false)
    {
        $this->info("Installing {$packageName}...");

        $packageManager = $this->detectPackageManager();

        // Adjust command for multiple packages if $packageName is an array
        $packageInstallString = is_array($packageName) ? implode(' ', $packageName) : $packageName;
        $devFlag = $isDev ? ($packageManager === 'yarn' ? ' --dev' : ' --save-dev') : '';

        $command = $packageManager === 'yarn' ?
            "yarn add {$packageInstallString}{$devFlag}" :
            "npm install {$packageInstallString}{$devFlag}";

        // Execute the command
        $process = Process::fromShellCommandline($command, null, null, STDIN, null);
        $process->setTty(Process::isTtySupported());
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->info("{$packageName} installed successfully.");
    }

    protected function installFilament()
    {
        $this->info('Installing FilamentPHP...');

        // Run the composer require command to install Filament
        $composerProcess = Process::fromShellCommandline('composer require filament/filament:^3.2 -W', null, null, null, null);
        $composerProcess->run();

        // Run the composer require command to install Filament
        if (! file_exists(config_path('filament.php'))) {
            $composerProcess = Process::fromShellCommandline('php artisan vendor:publish --tag=filament-config', null, null, null, null);
            $composerProcess->run();
        } else {
            $this->info('Filament configuration file already exists.');
        }

        $this->info($composerProcess->getOutput());

        // Running the command interactively
        $command = 'php artisan filament:install --panels';
        $process = new Process(explode(' ', $command), base_path(), null, STDIN, null);
        $process->setTty(Process::isTtySupported());
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        // Check if the command failed
        if (! $composerProcess->isSuccessful()) {
            throw new ProcessFailedException($composerProcess);
        }

        $this->info($composerProcess->getOutput());

        // Running the command interactively
        $command = 'php artisan make:filament-user';
        $process = new Process(explode(' ', $command), base_path(), null, STDIN, null);
        $process->setTty(Process::isTtySupported());
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        // Check if the command failed
        if (! $composerProcess->isSuccessful()) {
            throw new ProcessFailedException($composerProcess);
        }

        $this->info($composerProcess->getOutput());
    }
}
