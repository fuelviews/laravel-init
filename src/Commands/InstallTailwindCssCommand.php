<?php

namespace Fuelviews\Init\Commands;

use Fuelviews\Init\Traits\ExecutesShellCommands;
use Fuelviews\Init\Traits\ManagesNodePackages;
use Fuelviews\Init\Traits\PublishesStubFiles;

class InstallTailwindCssCommand extends BaseInitCommand
{
    use ExecutesShellCommands;
    use ManagesNodePackages;
    use PublishesStubFiles;

    protected $signature = 'init:tailwindcss {--force : Overwrite any existing files} {--dev : Install development versions of packages}';

    protected $description = 'Install TailwindCSS and related configuration for Laravel projects';

    public function handle(): int
    {
        $this->info('Installing TailwindCSS for Laravel...');
        

        // Publish configurations
        $this->startTask('Publishing TailwindCSS configurations');
        $published = $this->publishTailwindConfig();
        
        if ($published > 0) {
            $this->completeTask("Published {$published} configuration files");
        } else {
            $this->warn('Some files were not published (may already exist)');
        }

        // Install packages
        $this->startTask('Installing TailwindCSS packages');
        $packagesInstalled = $this->installTailwindPackages();
        
        if ($packagesInstalled) {
            $this->completeTask('TailwindCSS packages installed');
        } else {
            $this->failTask('Failed to install some packages');
            return self::FAILURE;
        }

        $this->info('✅ TailwindCSS installation completed successfully!');
        
        $this->info('You can now use Tailwind classes in your Blade templates');
        $this->info('Run "npm run dev" to compile your CSS');
        
        return self::SUCCESS;
    }

    private function publishTailwindConfig(): int
    {
        $published = 0;
        
        $configs = [
            'tailwind.config.js' => 'tailwind.config.js',
            'postcss.config.js' => 'postcss.config.js',
            'css/app.css' => 'resources/css/app.css',
        ];

        foreach ($configs as $stub => $destination) {
            if ($this->publishStubFile($stub, $destination)) {
                $published++;
            }
        }
        
        return $published;
    }

    private function installTailwindPackages(): bool
    {
        $devDependencies = [
            '@tailwindcss/forms',
            '@tailwindcss/typography',
            'autoprefixer',
            'postcss',
            'tailwindcss@3.4.17',
        ];

        // Check if packages are already installed
        $toInstall = [];
        foreach ($devDependencies as $package) {
            $packageName = explode('@', $package)[0]; // Handle versioned packages
            if (! $this->hasNodePackage($packageName)) {
                $toInstall[] = $package;
            } elseif ($this->output->isVerbose()) {
                $this->info("Package already installed: $packageName");
            }
        }

        if (empty($toInstall)) {
            $this->info('All TailwindCSS packages are already installed');
            return true;
        }

        return $this->installNodePackages($toInstall);
    }
}
