<?php

namespace Fuelviews\Init\Commands;

use Fuelviews\Init\Traits\ExecutesShellCommands;
use Fuelviews\Init\Traits\ManagesNodePackages;
use Fuelviews\Init\Traits\PublishesStubFiles;

class InstallViteCommand extends BaseInitCommand
{
    use ExecutesShellCommands;
    use ManagesNodePackages;
    use PublishesStubFiles;

    protected $signature = 'init:vite {--force : Overwrite any existing files} {--dev : Install development versions of packages}';

    protected $description = 'Install Vite and related configurations for Laravel projects';

    public function handle(): int
    {
        $this->info('Installing Vite for Laravel...');


        // Publish configuration
        $this->startTask('Publishing Vite configuration');
        $published = $this->publishViteConfig();

        if ($published) {
            $this->completeTask('Vite configuration published');
        } else {
            $this->warn('Some files were not published (may already exist)');
        }

        // Install packages
        $this->startTask('Installing Vite packages');
        $packagesInstalled = $this->installVitePackages();

        if ($packagesInstalled) {
            $this->completeTask('Vite packages installed');
        } else {
            $this->failTask('Failed to install some packages');

            return self::FAILURE;
        }

        $this->info('✓ Vite installation completed successfully!');

        $this->info('You can now run "npm run dev" to start the Vite development server');

        return self::SUCCESS;
    }

    private function publishViteConfig(): bool
    {
        return $this->publishStubFile('vite.config.js', 'vite.config.js');
    }

    private function installVitePackages(): bool
    {
        $devDependencies = [
            'dotenv',
            'laravel-vite-plugin',
            'vite',
        ];

        // Check if packages are already installed
        $toInstall = [];
        foreach ($devDependencies as $package) {
            if (! $this->hasNodePackage($package)) {
                $toInstall[] = $package;
            } elseif ($this->output->isVerbose()) {
                $this->info("Package already installed: $package");
            }
        }

        if (empty($toInstall)) {
            $this->info('All Vite packages are already installed');

            return true;
        }

        return $this->installNodePackages($toInstall);
    }
}
