<?php

namespace Fuelviews\LaravelInit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\comment;

class LaravelInitCommand extends Command
{
    protected $signature = 'laravel-init';

    protected $description = 'Initialize Laravel project with custom settings';

    public function handle(): int
    {
        // Check if vite.config.js already exists
        if (File::exists(base_path('vite.config.js'))) {
            // Confirm overwrite if file exists
            if (confirm(
                label: 'vite.config.js already exists. Do you want to overwrite it?',
                default: false
            )) {
                $this->publishViteConfig('overwrite');
            } else {
                $this->warn('Skipping vite.config.js installation.');
            }
        } else {
            // Confirm installation if file doesn't exist
            if (confirm(
                label: 'vite.config.js does not exist. Would you like to install it now?',
                default: true,
            )) {
                $this->publishViteConfig('install');
            }
        }

        $this->comment('All done');

        return self::SUCCESS;
    }

    protected function publishViteConfig($action)
    {
        $stubPath = __DIR__ . '/../../resources/vite.config.js';
        $destinationPath = base_path('vite.config.js');

        if ($action === 'overwrite') {
            $this->warn('Overwriting existing vite.config.js file.');
        }

        if (File::copy($stubPath, $destinationPath)) {
            $this->info('vite.config.js has been ' . ($action === 'overwrite' ? 'overwritten' : 'published') . ' successfully.');
        } else {
            $this->error('Failed to publish vite.config.js.');
        }
    }
}
