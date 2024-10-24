<?php

namespace Fuelviews\AppInit\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class InstallViteCommand extends Command
{
    protected $signature = 'app-init:vite {--force : Overwrite any existing files}';

    protected $description = 'Install Vite and related configurations for Laravel projects';

    public function handle(): void
    {
        $this->publishViteConfig();
        $this->installVitePackages();
        $this->info('Vite and related dependencies installed successfully.');
    }

    /**
     * Publish Vite configuration files
     */
    private function publishViteConfig(): void
    {
        $force = $this->option('force');

        $this->publishConfig('vite.config.js', $force);
    }

    /**
     * Install Vite and related development dependencies
     */
    private function installVitePackages(): void
    {
        $devDependencies = [
            'dotenv',
            'laravel-vite-plugin',
            'vite',
        ];

        $this->installNodePackages($devDependencies);
    }

    /**
     * Publish the configuration files
     */
    protected function publishConfig(string $configFileName, bool $force = false): void
    {
        $stubPath = __DIR__."/../../stubs/$configFileName.stub";
        $destinationPath = base_path($configFileName);

        if ($force || $this->option('force')) {
            \File::copy($stubPath, $destinationPath);
            $this->info("$configFileName has been installed or overwritten successfully.");
        } elseif (\File::exists($destinationPath)) {
            if ($this->confirm("$configFileName already exists. Do you want to overwrite it?", false)) {
                \File::copy($stubPath, $destinationPath);
                $this->info("$configFileName has been overwritten successfully.");
            } else {
                $this->warn("Skipping $configFileName installation.");
            }
        } else {
            \File::copy($stubPath, $destinationPath);
            $this->info("$configFileName has been installed successfully.");
        }
    }

    /**
     * Install Node.js development packages
     */
    private function installNodePackages(array $packageNames): void
    {
        $packageInstallString = implode(' ', $packageNames);
        $command = "npm install $packageInstallString --save-dev";

        $process = Process::fromShellCommandline($command, null, null, STDIN, null);
        $process->setTty(Process::isTtySupported());
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->info('Node packages installed successfully.');
    }
}
