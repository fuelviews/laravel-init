<?php

namespace Fuelviews\Init\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ConfigureEnvCommand extends Command
{
    protected $signature = 'init:env {--force : Overwrite any existing .env file}';

    protected $description = 'Modify .env contents';

    public function handle(): void
    {
        if (! file_exists(base_path('.env'))) {
            shell_exec('cp .env.example .env');
            shell_exec('php artisan key:generate');
        }

        $envPath = base_path('.env');

        $this->modifyEnvFile($envPath);

        shell_exec('herd secure');

        $this->info('.env file configured successfully.');
    }

    /**
     * Modify the contents of the .env file.
     */
    private function modifyEnvFile(string $envPath): void
    {
        $envContent = File::get($envPath);

        // Update APP_NAME to reflect the repository name (assume base directory name as repo name)
        $localProjectName = basename(base_path());

        // Replace dashes with spaces and capitalize each word
        $appName = '"'.Str::title(str_replace('-', ' ', "Local Dev ".$localProjectName)).'"';

        $envContent = preg_replace('/^APP_NAME=.*/m', "APP_NAME={$appName}", $envContent);

        // Update APP_URL to https://directory-name-here.test/
        $envContent = preg_replace('/^APP_URL=.*/m', "APP_URL=https://{$localProjectName}.test", $envContent);

        $envContent = preg_replace('/^LOG_DEPRECATIONS_CHANNEL=.*/m', 'LOG_DEPRECATIONS_CHANNEL=stack', $envContent);

        // Update database configuration
        $envContent = preg_replace('/^#?\s*DB_CONNECTION=.*/m', "\nDB_CONNECTION=pgsql", $envContent);
        $envContent = preg_replace('/^#?\s*DB_HOST=.*/m', 'DB_HOST=127.0.0.1', $envContent);
        $envContent = preg_replace('/^#?\s*DB_PORT=.*/m', 'DB_PORT=5432', $envContent);
        $envContent = preg_replace('/^#?\s*DB_DATABASE=.*/m', "DB_DATABASE=local-{$localProjectName}", $envContent);
        $envContent = preg_replace('/^#?\s*DB_USERNAME=.*/m', 'DB_USERNAME=root', $envContent);
        $envContent = preg_replace('/^#?\s*DB_PASSWORD=.*/m', 'DB_PASSWORD=', $envContent);

        // Write the modified content back to the .env file
        File::put($envPath, $envContent);

        // Generate app key if it's empty or missing
        if (preg_match('/^APP_KEY=\s*$/m', $envContent) || ! preg_match('/^APP_KEY=/m', $envContent)) {
            shell_exec('php artisan key:generate');
        }

        $this->info('Updated .env file contents.');
    }
}
