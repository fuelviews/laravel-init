<?php

namespace Fuelviews\Init\Traits;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

trait ManagesNodePackages
{
    protected function installNodePackages(array $packageNames, bool $dev = true): bool
    {
        if (empty($packageNames)) {
            return true;
        }

        $packageInstallString = implode(' ', $packageNames);
        $saveFlag = $dev ? '--save-dev' : '--save';
        $command = "npm install $packageInstallString $saveFlag";


        $this->startTask('Installing Node packages');
        
        try {
            $process = Process::fromShellCommandline($command, null, null, STDIN, null);
            $process->setTimeout(300); // 5 minute timeout
            
            if (Process::isTtySupported()) {
                $process->setTty(true);
            }

            if ($this->output->isVerbose()) {
                $process->run(function ($type, $buffer) {
                    $this->output->write($buffer);
                });
            } else {
                $progressBar = null;
                $process->run(function ($type, $buffer) use (&$progressBar) {
                    if ($progressBar === null && str_contains($buffer, 'added')) {
                        $this->line('');
                    }
                });
            }

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $this->completeTask('Node packages installed');
            return true;
        } catch (\Exception $e) {
            $this->failTask('Failed to install Node packages', $e->getMessage());
            return false;
        }
    }

    protected function updatePackageJson(callable $callback): bool
    {
        $packageJsonPath = base_path('package.json');

        if (! file_exists($packageJsonPath)) {
            $this->warn('package.json file not found.');
            return false;
        }


        try {
            $content = json_decode(file_get_contents($packageJsonPath), true, 512, JSON_THROW_ON_ERROR);
            $originalContent = $content;
            
            $content = $callback($content);
            
            if ($content === $originalContent) {
                $this->info('package.json is already up to date');
                return true;
            }

            file_put_contents(
                $packageJsonPath,
                json_encode($content, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL
            );

            $this->info('✓ Updated package.json');
            return true;
        } catch (\Exception $e) {
            $this->error('Failed to update package.json: ' . $e->getMessage());
            return false;
        }
    }

    protected function updateNodeScripts(array $scripts): bool
    {
        return $this->updatePackageJson(function ($content) use ($scripts) {
            if (! isset($content['scripts'])) {
                $content['scripts'] = [];
            }

            foreach ($scripts as $name => $command) {
                if (! isset($content['scripts'][$name])) {
                    $content['scripts'][$name] = $command;
                    $this->info("  Added script: $name");
                }
            }

            return $content;
        });
    }

    protected function hasNodePackage(string $packageName): bool
    {
        $packageJsonPath = base_path('package.json');
        
        if (! file_exists($packageJsonPath)) {
            return false;
        }

        try {
            $content = json_decode(file_get_contents($packageJsonPath), true);
            
            $inDependencies = isset($content['dependencies'][$packageName]);
            $inDevDependencies = isset($content['devDependencies'][$packageName]);
            
            return $inDependencies || $inDevDependencies;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getPackageManager(): string
    {
        if (file_exists(base_path('yarn.lock'))) {
            return 'yarn';
        }
        
        if (file_exists(base_path('pnpm-lock.yaml'))) {
            return 'pnpm';
        }
        
        return 'npm';
    }
}