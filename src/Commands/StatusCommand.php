<?php

namespace Fuelviews\Init\Commands;

use Fuelviews\Init\Traits\ExecutesShellCommands;
use Fuelviews\Init\Traits\ManagesNodePackages;
use Fuelviews\Init\Traits\PublishesStubFiles;
use Illuminate\Support\Facades\File;

class StatusCommand extends BaseInitCommand
{
    use ExecutesShellCommands;
    use ManagesNodePackages;
    use PublishesStubFiles;

    protected $signature = 'init:status';

    protected $description = 'Check the installation status of Laravel Init packages and configurations';

    public function handle(): int
    {
        $this->info('Laravel Init Package Status');
        $this->line('===========================');
        $this->newLine();

        $this->checkConfigurationFiles();
        $this->newLine();
        
        $this->checkNodePackages();
        $this->newLine();
        
        $this->checkComposerPackages();
        $this->newLine();
        
        $this->checkAvailableStubs();
        
        return self::SUCCESS;
    }

    private function checkConfigurationFiles(): void
    {
        $this->info('Configuration Files:');
        
        $configs = [
            'vite.config.js' => 'Vite configuration',
            'tailwind.config.js' => 'Tailwind CSS configuration',
            'postcss.config.js' => 'PostCSS configuration',
            '.prettierrc' => 'Prettier configuration',
            '.prettierignore' => 'Prettier ignore file',
            'resources/css/app.css' => 'Application CSS',
        ];

        foreach ($configs as $file => $description) {
            $path = base_path($file);
            $exists = File::exists($path);
            
            $status = $exists ? '✓' : '✗';
            $color = $exists ? 'info' : 'comment';
            
            $this->line("  [{$status}] {$description}");
            
            if ($this->output->isVerbose()) {
                $this->line("      Path: {$path}", $color);
                if ($exists) {
                    $size = File::size($path);
                    $modified = date('Y-m-d H:i:s', File::lastModified($path));
                    $this->line("      Size: {$size} bytes, Modified: {$modified}", 'comment');
                }
            }
        }
    }

    private function checkNodePackages(): void
    {
        $this->info('Node Packages:');
        
        if (! File::exists(base_path('package.json'))) {
            $this->warn('  package.json not found');

            return;
        }

        $packages = [
            // Vite
            'vite' => 'Vite build tool',
            'laravel-vite-plugin' => 'Laravel Vite integration',
            'dotenv' => 'Environment variables',
            // Tailwind
            'tailwindcss' => 'Tailwind CSS framework',
            '@tailwindcss/forms' => 'Tailwind forms plugin',
            '@tailwindcss/typography' => 'Tailwind typography plugin',
            'autoprefixer' => 'PostCSS autoprefixer',
            'postcss' => 'PostCSS processor',
            // Prettier
            'prettier' => 'Code formatter',
            'prettier-plugin-blade' => 'Blade template formatter',
            'prettier-plugin-tailwindcss' => 'Tailwind class sorter',
        ];

        foreach ($packages as $package => $description) {
            $installed = $this->hasNodePackage($package);
            $status = $installed ? '✓' : '✗';
            $color = $installed ? 'info' : 'comment';
            
            $this->line("  [{$status}] {$package}", $color);
            
            if ($this->output->isVerbose()) {
                $this->line("      {$description}", 'comment');
            }
        }
    }

    private function checkComposerPackages(): void
    {
        $this->info('Composer Packages:');
        
        $packages = [
            'fuelviews/laravel-sabhero-wrapper' => 'Sabhero wrapper',
            'fuelviews/laravel-cloudflare-cache' => 'Cloudflare cache',
            'fuelviews/laravel-robots-txt' => 'Robots.txt manager',
            'fuelviews/laravel-sitemap' => 'Sitemap generator',
            'ralphjsmit/laravel-seo' => 'SEO tools',
            'ralphjsmit/laravel-glide' => 'Image manipulation',
            'livewire/livewire' => 'Livewire framework',
            'spatie/laravel-google-fonts' => 'Google Fonts loader',
            'spatie/image-optimizer' => 'Image optimizer (dev)',
        ];

        foreach ($packages as $package => $description) {
            $installed = $this->isComposerPackageInstalled($package);
            $status = $installed ? '✓' : '✗';
            $color = $installed ? 'info' : 'comment';
            
            $this->line("  [{$status}] {$package}", $color);
            
            if ($this->output->isVerbose()) {
                $this->line("      {$description}", 'comment');
            }
        }
    }

    private function checkAvailableStubs(): void
    {
        $this->info('Available Stubs:');
        
        $stubs = $this->getAvailableStubs();
        
        if (empty($stubs)) {
            $this->warn('  No stub files found');

            return;
        }

        foreach ($stubs as $stub) {
            $this->line("  • {$stub}");
        }
        
        $this->newLine();
        $this->line('Run "php artisan vendor:publish --tag=init-stubs" to publish all stubs');
    }

    private function isComposerPackageInstalled(string $package): bool
    {
        $composerFile = base_path('composer.json');
        
        if (! File::exists($composerFile)) {
            return false;
        }

        try {
            $content = json_decode(File::get($composerFile), true);
            
            $inRequire = isset($content['require'][$package]);
            $inRequireDev = isset($content['require-dev'][$package]);
            
            return $inRequire || $inRequireDev;
        } catch (\Exception $e) {
            return false;
        }
    }
}
