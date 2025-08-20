<?php

namespace Fuelviews\Init\Traits;

use Illuminate\Support\Facades\File;

trait PublishesStubFiles
{
    protected function publishStubFile(string $stubFile, string $destination, bool $force = null): bool
    {
        $force = $force ?? $this->isForce();
        $stubPath = $this->resolveStubPath($stubFile);
        $destinationPath = $this->resolveDestinationPath($destination);

        if ($this->isDryRun()) {
            $this->info("[DRY RUN] Would publish: $stubFile to $destinationPath");
            return true;
        }

        if (! File::exists($stubPath)) {
            $this->error("Stub file not found: $stubPath");
            return false;
        }

        $this->ensureDirectoryExists(dirname($destinationPath));

        if (File::exists($destinationPath) && ! $force) {
            $this->warn("File already exists: $destinationPath (use --force to overwrite)");
            return false;
        }

        if (File::exists($destinationPath) && $force) {
            $this->backupFile($destinationPath);
        }

        try {
            File::copy($stubPath, $destinationPath);
            $this->info("✓ Published: " . $this->getRelativePath($destinationPath));
            return true;
        } catch (\Exception $e) {
            $this->error("Failed to publish $stubFile: " . $e->getMessage());
            return false;
        }
    }

    protected function publishMultipleStubs(array $stubs, bool $force = null): int
    {
        $published = 0;
        
        foreach ($stubs as $stub => $destination) {
            if ($this->publishStubFile($stub, $destination, $force)) {
                $published++;
            }
        }
        
        return $published;
    }

    protected function resolveStubPath(string $stubFile): string
    {
        if (! str_ends_with($stubFile, '.stub')) {
            $stubFile .= '.stub';
        }

        $basePath = dirname(dirname(__DIR__)) . '/stubs/';
        
        return $basePath . $stubFile;
    }

    protected function resolveDestinationPath(string $destination): string
    {
        if (str_starts_with($destination, 'resources/')) {
            return resource_path(substr($destination, 10));
        }
        
        if (str_starts_with($destination, 'config/')) {
            return config_path(substr($destination, 7));
        }
        
        if (str_starts_with($destination, 'database/')) {
            return database_path(substr($destination, 9));
        }
        
        return base_path($destination);
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
            
            if ($this->output->isVerbose()) {
                $this->info("Created directory: $directory");
            }
        }
    }

    protected function backupFile(string $filePath): void
    {
        $backupPath = $filePath . '.backup.' . date('YmdHis');
        
        try {
            File::copy($filePath, $backupPath);
            
            if ($this->output->isVerbose()) {
                $this->info("Backed up existing file to: " . basename($backupPath));
            }
        } catch (\Exception $e) {
            $this->warn("Could not backup file: " . $e->getMessage());
        }
    }

    protected function getRelativePath(string $path): string
    {
        $basePath = base_path();
        
        if (str_starts_with($path, $basePath)) {
            return substr($path, strlen($basePath) + 1);
        }
        
        return $path;
    }

    protected function stubExists(string $stubFile): bool
    {
        return File::exists($this->resolveStubPath($stubFile));
    }

    protected function getAvailableStubs(): array
    {
        $stubPath = dirname(dirname(__DIR__)) . '/stubs/';
        
        if (! File::exists($stubPath)) {
            return [];
        }

        $stubs = [];
        $files = File::allFiles($stubPath);
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'stub') {
                $relativePath = str_replace($stubPath, '', $file->getPathname());
                $stubs[] = str_replace('.stub', '', $relativePath);
            }
        }
        
        return $stubs;
    }
}