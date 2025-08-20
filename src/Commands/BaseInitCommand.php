<?php

namespace Fuelviews\Init\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

abstract class BaseInitCommand extends Command
{
    protected bool $dryRun = false;

    protected function addCommonOptions(): void
    {
        $this->getDefinition()->addOption(
            new \Symfony\Component\Console\Input\InputOption(
                'force',
                null,
                \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
                'Overwrite any existing files'
            )
        );


        $this->getDefinition()->addOption(
            new \Symfony\Component\Console\Input\InputOption(
                'dev',
                null,
                \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
                'Install development versions of packages (dev-main, dev-master, etc.)'
            )
        );

    }

    protected function isForce(): bool
    {
        return $this->option('force') ?? false;
    }

    protected function isDev(): bool
    {
        return $this->option('dev') ?? false;
    }

    protected function publishConfig(string $configFileName, bool $force = null): bool
    {
        $force = $force ?? $this->isForce();
        $stubPath = $this->getStubPath($configFileName);
        $destinationPath = base_path($configFileName);


        if (! File::exists($stubPath)) {
            $this->error("Stub file not found: $stubPath");

            return false;
        }

        if (File::exists($destinationPath) && ! $force) {
            $this->warn("File already exists: $destinationPath (use --force to overwrite)");

            return false;
        }

        try {
            File::copy($stubPath, $destinationPath);
            $this->info("✓ Published: $configFileName");

            return true;
        } catch (\Exception $e) {
            $this->error("Failed to publish $configFileName: " . $e->getMessage());

            return false;
        }
    }

    protected function publishStubToPath(string $stubFile, string $destinationPath, bool $force = null): bool
    {
        $force = $force ?? $this->isForce();
        $stubPath = $this->getStubPath($stubFile);


        if (! File::exists($stubPath)) {
            $this->error("Stub file not found: $stubPath");

            return false;
        }

        $destinationDir = dirname($destinationPath);
        if (! File::exists($destinationDir)) {
            File::makeDirectory($destinationDir, 0755, true);
        }

        if (File::exists($destinationPath) && ! $force) {
            $this->warn("File already exists: $destinationPath (use --force to overwrite)");

            return false;
        }

        try {
            File::copy($stubPath, $destinationPath);
            $this->info("✓ Published: " . basename($destinationPath));

            return true;
        } catch (\Exception $e) {
            $this->error("Failed to publish $stubFile: " . $e->getMessage());

            return false;
        }
    }

    protected function getStubPath(string $stubFile): string
    {
        $stubFile = str_ends_with($stubFile, '.stub') ? $stubFile : "$stubFile.stub";

        return __DIR__ . "/../../stubs/$stubFile";
    }

    protected function startTask(string $description): void
    {
        if ($this->output->isVerbose()) {
            $this->line("Starting: $description");
        }
    }

    protected function completeTask(string $description): void
    {
        $this->info("✓ $description");
    }

    protected function failTask(string $description, string $error = null): void
    {
        $message = "✗ $description";
        if ($error) {
            $message .= ": $error";
        }
        $this->error($message);
    }
}
