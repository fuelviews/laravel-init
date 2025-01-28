<?php

namespace Fuelviews\Init\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    protected $signature = 'init:install {--force : Overwrite any existing files}';

    protected $description = 'Install all Fuelviews packages, TailwindCSS, Vite, Prettier, and other dependencies';

    public function handle(): void
    {
        $this->info('Starting the installation process...');

        // Check if the --force flag is passed
        $forceOption = $this->option('force') ? ['--force' => true] : [];

        $this->call('init:changelog', $forceOption);
        $this->call('init:vite', $forceOption);
        $this->call('init:tailwind', $forceOption);
        $this->call('init:prettier', $forceOption);
        $this->call('init:git-dot-files', $forceOption);
        $this->call('init:composer-packages', $forceOption);

        // Run the migrations after ensuring environment variables are loaded
        $this->call('migrate', $forceOption);

        $this->call('optimize:clear');

        $this->runShellCommand('npm run build');

        $this->info('All packages and dependencies installed successfully.');
    }

    /**
     * Run shell commands in the system
     */
    private function runShellCommand($command): void
    {
        $process = Process::fromShellCommandline($command);

        $process->setTty(Process::isTtySupported());

        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
