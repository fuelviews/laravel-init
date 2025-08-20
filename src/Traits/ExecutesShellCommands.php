<?php

namespace Fuelviews\Init\Traits;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

trait ExecutesShellCommands
{
    protected int $defaultTimeout = 300; // 5 minutes

    protected function runShellCommand(string $command, int $timeout = null): bool
    {

        $this->startTask("Executing: $command");

        try {
            $process = Process::fromShellCommandline($command);
            $process->setTimeout($timeout ?? $this->defaultTimeout);

            if (Process::isTtySupported()) {
                $process->setTty(true);
            }

            if ($this->output->isVerbose()) {
                $process->run(function ($type, $buffer) {
                    $this->output->write($buffer);
                });
            } else {
                $process->run();
            }

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $this->completeTask('Command executed successfully');
            return true;
        } catch (ProcessFailedException $e) {
            $this->failTask('Command failed', $e->getMessage());
            
            if ($this->output->isVerbose()) {
                $this->error('Output: ' . $e->getProcess()->getOutput());
                $this->error('Error Output: ' . $e->getProcess()->getErrorOutput());
            }
            
            return false;
        } catch (\Exception $e) {
            $this->failTask('Command failed', $e->getMessage());
            return false;
        }
    }

    protected function runShellCommandWithOutput(string $command, int $timeout = null): ?string
    {

        try {
            $process = Process::fromShellCommandline($command);
            $process->setTimeout($timeout ?? $this->defaultTimeout);
            $process->run();

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            return $process->getOutput();
        } catch (\Exception $e) {
            $this->error("Failed to execute command: " . $e->getMessage());
            return null;
        }
    }

    protected function commandExists(string $command): bool
    {
        $checkCommand = match (PHP_OS_FAMILY) {
            'Windows' => "where $command",
            default => "which $command",
        };

        $process = Process::fromShellCommandline($checkCommand);
        $process->run();

        return $process->isSuccessful();
    }

    protected function runComposerCommand(string $command, int $timeout = null): bool
    {
        $fullCommand = "composer $command";
        
        // Add dev preferences if in dev mode
        if ($this->isDev()) {
            $fullCommand .= " --prefer-source";
        }
        
        if ($this->output->isQuiet()) {
            $fullCommand .= ' --quiet';
        } elseif ($this->output->isVerbose()) {
            $fullCommand .= ' -vvv';
        }

        return $this->runShellCommand($fullCommand, $timeout ?? 600); // 10 minute default for composer
    }

    protected function runArtisanCommand(string $command, array $arguments = []): bool
    {

        try {
            $exitCode = $this->call($command, $arguments);
            return $exitCode === 0;
        } catch (\Exception $e) {
            $this->error("Failed to run artisan command: " . $e->getMessage());
            return false;
        }
    }

    protected function isStorageLinked(): bool
    {
        $publicStorageLink = public_path('storage');
        $storagePath = storage_path('app/public');

        return is_link($publicStorageLink) && readlink($publicStorageLink) === $storagePath;
    }

    protected function ensureStorageLink(): bool
    {
        if ($this->isStorageLinked()) {
            if ($this->output->isVerbose()) {
                $this->info('Storage link already exists');
            }
            return true;
        }

        return $this->runArtisanCommand('storage:link');
    }
}