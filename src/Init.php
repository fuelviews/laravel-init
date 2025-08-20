<?php

namespace Fuelviews\Init;

use Illuminate\Support\Facades\File;

class Init
{
    public function getAvailableStubs(): array
    {
        $stubPath = __DIR__.'/../stubs';
        
        if (! File::exists($stubPath)) {
            return [];
        }

        $stubs = [];
        $files = File::allFiles($stubPath);
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'stub') {
                $relativePath = str_replace($stubPath.'/', '', $file->getPathname());
                $stubName = str_replace('.stub', '', $relativePath);
                
                $stubs[$stubName] = $this->getStubDescription($stubName);
            }
        }
        
        return $stubs;
    }

    public function getPackageInfo(): array
    {
        $composerFile = __DIR__.'/../composer.json';
        if (file_exists($composerFile)) {
            return json_decode(file_get_contents($composerFile), true) ?? [];
        }

        return [];
    }

    public function getVersion(): string
    {
        $packageInfo = $this->getPackageInfo();

        return $packageInfo['version'] ?? 'dev-main';
    }

    public function getAvailableCommands(): array
    {
        return [
            'init:install' => 'Install all Fuelviews packages and dependencies',
            'init:status' => 'Check installation status of packages and configurations',
            'init:changelog' => 'Install changelog configuration',
            'init:git-dot-files' => 'Install git configuration files',
            'init:prettier' => 'Install Prettier configuration',
            'init:tailwindcss' => 'Install TailwindCSS configuration',
            'init:vite' => 'Install Vite configuration',
            'init:composer-packages' => 'Install Composer packages',
            'init:env' => 'Configure environment variables',
            'init:images' => 'Process and optimize images',
        ];
    }

    public function hasStub(string $stubName): bool
    {
        $stubPath = __DIR__.'/../stubs/'.$stubName.'.stub';

        return file_exists($stubPath);
    }

    public function getStubPath(string $stubName): string
    {
        return __DIR__.'/../stubs/'.$stubName.'.stub';
    }

    public function getPublishingTags(): array
    {
        return [
            'init-all' => 'All configuration files',
            'init-stubs' => 'All configuration stubs',
            'init-vite' => 'Vite configuration only',
            'init-tailwind' => 'Tailwind CSS and PostCSS configurations',
            'init-styles' => 'CSS files only',
        ];
    }

    public function getInstalledPackages(): array
    {
        $composerFile = base_path('composer.json');
        
        if (! File::exists($composerFile)) {
            return [];
        }

        try {
            $content = json_decode(File::get($composerFile), true);
            
            $packages = [];
            
            if (isset($content['require'])) {
                foreach ($content['require'] as $package => $version) {
                    if (str_starts_with($package, 'fuelviews/') || str_starts_with($package, 'ralphjsmit/') || $package === 'livewire/livewire') {
                        $packages[$package] = $version;
                    }
                }
            }
            
            return $packages;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getConfigurationStatus(): array
    {
        $configs = [
            'vite.config.js' => 'Vite configuration',
            'tailwind.config.js' => 'Tailwind CSS configuration',
            'postcss.config.js' => 'PostCSS configuration',
            '.prettierrc' => 'Prettier configuration',
            '.prettierignore' => 'Prettier ignore file',
            'resources/css/app.css' => 'Application CSS',
        ];

        $status = [];
        foreach ($configs as $file => $description) {
            $path = base_path($file);
            $status[$file] = [
                'description' => $description,
                'exists' => File::exists($path),
                'path' => $path,
            ];
        }

        return $status;
    }

    private function getStubDescription(string $stubName): string
    {
        $descriptions = [
            'vite.config.js' => 'Vite configuration for Laravel',
            'tailwind.config.js' => 'Tailwind CSS configuration',
            'postcss.config.js' => 'PostCSS configuration',
            'css/app.css' => 'Base CSS file with Tailwind imports',
            '.prettierrc' => 'Prettier formatting configuration',
            '.prettierignore' => 'Prettier ignore rules',
        ];

        return $descriptions[$stubName] ?? 'Configuration file';
    }
}
