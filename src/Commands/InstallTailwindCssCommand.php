<?php

namespace Fuelviews\Init\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class InstallTailwindCssCommand extends Command
{
    protected $signature = 'init:tailwindcss {--force : Overwrite any existing files}: Specify the Tailwind CSS version to install}';

    protected $description = 'Install TailwindCSS and related configuration for Laravel projects';

    public function handle(): void
    {
        $this->publishTailwindConfig();
        $this->installTailwindPackages();
        $this->info('TailwindCSS installed successfully.');
    }

    private function publishTailwindConfig(): void
    {
        $force = $this->option('force');

        $this->publishConfig('tailwind.config.js', $force);
        $this->publishConfig('postcss.config.js', $force);

        $this->publishAppCss($force);
    }

    /**
     * Install TailwindCSS and related PostCSS packages.
     */
    private function installTailwindPackages(): void
    {
        $devDependencies = [
            '@tailwindcss/forms',
            '@tailwindcss/typography',
            'autoprefixer',
            'postcss',
            'tailwindcss@3.4.17',
        ];

        $this->installNodePackages($devDependencies);
    }

    protected function publishConfig(string $configFileName, bool $force = false): void
    {
        $stubPath = __DIR__."/../../stubs/$configFileName.stub";
        $destinationPath = base_path($configFileName);

        if ($force || $this->option('force')) {
            \File::copy($stubPath, $destinationPath);
            $this->info("$configFileName has been installed or overwritten successfully.");
        }
    }

    protected function publishAppCss(bool $force): void
    {
        $stubPath = __DIR__.'/../../stubs/css/app.css.stub';
        $destinationPath = resource_path('css/app.css');

        if (! \File::exists(dirname($destinationPath))) {
            \File::makeDirectory(dirname($destinationPath), 0755, true);
        }

        if ($force || $this->option('force')) {
            \File::copy($stubPath, $destinationPath);
            $this->info('css/app.css file has been installed or overwritten successfully.');
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
}
