<?php

namespace Fuelviews\Init\Commands;

use Fuelviews\Init\Traits\ExecutesShellCommands;
use Fuelviews\Init\Traits\ManagesNodePackages;
use Fuelviews\Init\Traits\PublishesStubFiles;

class InstallPrettierCommand extends BaseInitCommand
{
    use ExecutesShellCommands;
    use ManagesNodePackages;
    use PublishesStubFiles;

    protected $signature = 'init:prettier {--force : Overwrite any existing files} {--dev : Install development versions of packages}';

    protected $description = 'Install Prettier and associated plugins for Laravel projects';

    public function handle(): int
    {
        $this->info('Installing Prettier for Laravel...');
        

        // Publish configurations
        $this->startTask('Publishing Prettier configuration files');
        $published = $this->publishPrettierConfig();
        
        if ($published > 0) {
            $this->completeTask("Published {$published} configuration files");
        } else {
            $this->warn('Some config files were not published (may already exist)');
        }

        // Install packages
        $this->startTask('Installing Prettier packages');
        $packagesInstalled = $this->installPrettierPackages();
        
        if (! $packagesInstalled) {
            $this->failTask('Failed to install some packages');

            return self::FAILURE;
        }

        // Update package.json scripts
        $this->startTask('Adding format script to package.json');
        $scriptsUpdated = $this->updateNodeScripts([
            'format' => 'npx prettier --write resources/views/',
        ]);

        if ($scriptsUpdated) {
            $this->completeTask('Added format script to package.json');
        }

        $this->info('✅ Prettier installation completed successfully!');
        
        $this->info('You can now run "npm run format" to format your Blade templates');
        
        return self::SUCCESS;
    }

    private function publishPrettierConfig(): int
    {
        $published = 0;
        
        // Note: These stub files need to be created if they don't exist
        $configs = [
            '.prettierrc' => '.prettierrc',
            '.prettierignore' => '.prettierignore',
        ];

        foreach ($configs as $stub => $destination) {
            if ($this->stubExists($stub)) {
                if ($this->publishStubFile($stub, $destination)) {
                    $published++;
                }
            } else {
                $this->warn("Stub file not found: {$stub}.stub");
            }
        }
        
        return $published;
    }

    private function installPrettierPackages(): bool
    {
        $devDependencies = [
            'prettier',
            'prettier-plugin-blade',
            'prettier-plugin-tailwindcss',
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
            $this->info('All Prettier packages are already installed');

            return true;
        }

        return $this->installNodePackages($toInstall);
    }
}
