<?php

namespace Fuelviews\Init;

class Init
{
    public function getAvailableStubs(): array
    {
        return [
            'vite.config.js' => 'Vite configuration for Laravel',
            'tailwind.config.js' => 'Tailwind CSS configuration',
            'postcss.config.js' => 'PostCSS configuration',
            'css/app.css' => 'Base CSS file with Tailwind imports',
        ];
    }

    public function getPackageInfo(): array
    {
        $composerFile = __DIR__.'/../composer.json';
        if (file_exists($composerFile)) {
            return json_decode(file_get_contents($composerFile), true);
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
            'init:changelog' => 'Install changelog configuration',
            'init:git-dot-files' => 'Install git configuration files',
            'init:prettier' => 'Install Prettier configuration',
            'init:tailwind' => 'Install Tailwind CSS',
            'init:vite' => 'Install Vite configuration',
            'init:composer-packages' => 'Install Composer packages',
            'init:configure-env' => 'Configure environment variables',
            'init:process-images' => 'Process and optimize images',
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
}
