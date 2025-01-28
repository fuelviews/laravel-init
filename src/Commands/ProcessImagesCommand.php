<?php

namespace Fuelviews\Init\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class ProcessImagesCommand extends Command
{
    protected $signature = 'init:images {--force : Overwrite any existing files}';

    protected $description = 'Process images in the public/images/ folder. Unifies naming, fixes extensions, and optimizes images.';

    private $totalSavedSpace = 0;

    private $minSavings = 3120; // Minimum savings in bytes (3 KB)git check

    public function handle(): void
    {
        $this->info('Processing images in the public/images/ folder...');

        $imagePath = public_path('images');

        if (! File::exists($imagePath)) {
            $this->error('The public/images/ directory does not exist.');

            return;
        }

        $files = File::files($imagePath);

        if (empty($files)) {
            $this->info('No images found to process.');

            return;
        }

        foreach ($files as $file) {
            if ($file->getExtension() === 'svg') {
                $this->warn("Skipping {$file->getFilename()}: SVG files are not processed.");

                continue;
            }

            $this->processImage($file);
        }

        $this->info('Image processing and optimization completed.');
        $this->info('Total space saved: '.$this->formatSize($this->totalSavedSpace));
    }

    private function processImage($file): void
    {
        $originalFilename = $file->getFilename();
        $filePath = $file->getPathname();

        $this->info("Processing file: {$filePath}");

        try {
            // Get original file size
            $originalSize = filesize($filePath);

            // Determine the correct file extension
            $image = Image::make($filePath);
            $mime = $image->mime(); // Get MIME type
            $correctExtension = $this->getCorrectExtension($mime);

            if (! $correctExtension) {
                $this->warn("Skipping {$originalFilename}: Unknown MIME type.");

                return;
            }

            // Check if renaming is required
            $newFilename = $this->generateNewName($originalFilename, $correctExtension);
            $newFilePath = $file->getPath().DIRECTORY_SEPARATOR.$newFilename;

            if ($originalFilename !== $newFilename) {
                if (File::exists($newFilePath) && ! $this->option('force')) {
                    $this->warn("Skipping renaming {$originalFilename}: {$newFilename} already exists.");
                } else {
                    if (! File::move($filePath, $newFilePath)) {
                        $this->error("Failed to rename {$filePath} to {$newFilePath}");

                        return;
                    }
                    $this->info("Renamed {$originalFilename} -> {$newFilename}");
                    $filePath = $newFilePath; // Update file path for optimization
                }
            }

            // Optimize the file (only if savings > 5 KB)
            if (File::exists($filePath)) {
                $this->optimizeImage($filePath, $originalSize);
            } else {
                $this->error("File {$filePath} not found after renaming.");
            }
        } catch (\Exception $e) {
            $this->error("Failed to process {$originalFilename}: {$e->getMessage()}");
        }
    }

    private function getCorrectExtension(string $mime): ?string
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        return $extensions[$mime] ?? null;
    }

    private function generateNewName(string $filename, string $correctExtension): string
    {
        $fileInfo = pathinfo($filename);

        // Convert base name to lowercase and apply correct extension
        return strtolower($fileInfo['filename']).'.'.$correctExtension;
    }

    private function optimizeImage(string $filePath, int $originalSize): void
    {
        $optimizerChain = OptimizerChainFactory::create();

        if (! File::exists($filePath)) {
            $this->error("File {$filePath} does not exist for optimization.");

            return;
        }

        // Optimize the image
        try {
            $optimizerChain->optimize($filePath);
            $optimizedSize = filesize($filePath);

            // Calculate space saved
            $spaceSaved = $originalSize - $optimizedSize;

            if ($spaceSaved >= $this->minSavings) {
                $this->totalSavedSpace += $spaceSaved;
                $this->info("Optimized {$filePath}: Saved ".$this->formatSize($spaceSaved));
            } else {
                $this->warn("Skipped optimization for {$filePath}: Savings less than 5 KB.");
                // Revert to original size if savings are insufficient
                File::put($filePath, File::get($filePath));
            }
        } catch (\Exception $e) {
            $this->warn("Optimization failed for {$filePath}: {$e->getMessage()}");
        }
    }

    private function formatSize(int $size): string
    {
        if ($size >= 1 << 30) {
            return number_format($size / (1 << 30), 2).' GB';
        }

        if ($size >= 1 << 20) {
            return number_format($size / (1 << 20), 2).' MB';
        }

        if ($size >= 1 << 10) {
            return number_format($size / (1 << 10), 2).' KB';
        }

        return $size.' bytes';
    }
}
