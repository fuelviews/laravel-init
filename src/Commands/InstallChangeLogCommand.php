<?php

namespace Fuelviews\AppInit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallChangeLogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app-init:changelog {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes a CHANGELOG.md file with default content';

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws \JsonException
     */
    public function handle()
    {
        // Get the repo name from composer.json file
        $composerFilePath = base_path('composer.json');

        if (! File::exists($composerFilePath)) {
            $this->error('The composer.json file does not exist!');

            return 1;
        }

        $composerContent = json_decode(File::get($composerFilePath), true, 512, JSON_THROW_ON_ERROR);

        if (isset($composerContent['name'])) {
            $fullRepoName = $composerContent['name'];

            // Split by "/" and take the second part, ignoring the vendor/namespace part
            $parts = explode('/', $fullRepoName);
            $repoName = end($parts); // Only use the part after the "/"
        } else {
            $this->error('Repository name not found in composer.json!');

            return 1;
        }

        $filePath = base_path('CHANGELOG.md');
        $content = "# Changelog\n\nAll notable changes to `{$repoName}` will be documented in this file.";

        // Check if file exists and if the --force option is not passed
        if (File::exists($filePath) && ! $this->option('force')) {
            $this->error('The CHANGELOG.md file already exists! Use --force to overwrite.');

            return 1;
        }

        // Write the file (it will overwrite if --force is passed or if the file doesn't exist)
        File::put($filePath, $content);
        $this->info("CHANGELOG.md file has been created successfully with repo name: {$repoName}!");

        return 0;
    }
}
