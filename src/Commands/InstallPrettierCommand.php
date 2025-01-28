<?php

namespace Fuelviews\Init\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class InstallPrettierCommand extends Command
{
    protected $signature = 'init:prettier {--force : Overwrite any existing files}';

    protected $description = 'Install Prettier and associated plugins for Laravel projects';

    public function handle(): void
    {
        $this->publishPrettierConfig();
        $this->installPrettierPackages();
        $this->updateNodeScripts();
        $this->info('Prettier installed successfully.');
    }

    private function publishPrettierConfig(): void
    {
        $force = $this->option('force');

        $this->publishConfig('.prettierrc', $force);
        $this->publishConfig('.prettierignore', $force);
    }

    /**
     * Install Prettier and its related plugins.
     */
    private function installPrettierPackages(): void
    {
        $devDependencies = [
            'prettier',
            'prettier-plugin-blade',
            'prettier-plugin-tailwindcss',
        ];

        $this->installNodePackages($devDependencies);
    }

    /**
     * Add Prettier format script to package.json
     */
    private function updateNodeScripts(): void
    {
        $this->updateNodeScriptsFile(function ($scripts) {
            if (! isset($scripts['format'])) {
                $scripts['format'] = 'npx prettier --write resources/views/';
            }

            return $scripts;
        });
    }

    private function publishConfig(string $configFileName, bool $force = false): void
    {
        $stubPath = __DIR__."/../../stubs/$configFileName.stub";
        $destinationPath = base_path($configFileName);

        if ($force || $this->option('force')) {
            \File::copy($stubPath, $destinationPath);
            $this->info("$configFileName has been installed or overwritten successfully.");
        }
    }

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

    private function updateNodeScriptsFile(callable $callback): void
    {
        $packageJsonPath = base_path('package.json');

        if (! file_exists($packageJsonPath)) {
            $this->warn('package.json file not found.');

            return;
        }

        $content = json_decode(file_get_contents($packageJsonPath), true, 512, JSON_THROW_ON_ERROR);

        $content['scripts'] = $callback(
            array_key_exists('scripts', $content) ? $content['scripts'] : []
        );

        file_put_contents(
            $packageJsonPath,
            json_encode($content, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL
        );

        $this->info('Updated package.json scripts section.');
    }
}
