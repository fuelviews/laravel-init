<?php

namespace Fuelviews\AppInit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallGitDotFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app-init:git-dot-files {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes git dot files with default settings';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Define the source and destination paths for the files
        $files = [
            '.gitattributes.stub' => base_path('.gitattributes'),
            '.gitignore.stub' => base_path('.gitignore'),
        ];

        // Define the stub/template location
        $stubsPath = __DIR__.'/../../stubs/';

        foreach ($files as $filename => $destinationPath) {
            // Check if the stub file exists
            $stubFilePath = $stubsPath.$filename;
            if (! File::exists($stubFilePath)) {
                $this->error("Stub file for {$filename} not found. $stubsPath");

                return Command::FAILURE;
            }

            // Copy the stub file to the destination
            File::copy($stubFilePath, $destinationPath);

            $this->info("{$filename} has been published.");
        }

        return Command::SUCCESS;
    }
}
