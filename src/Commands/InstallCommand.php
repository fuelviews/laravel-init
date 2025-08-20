<?php

namespace Fuelviews\Init\Commands;

use Fuelviews\Init\Traits\ExecutesShellCommands;

class InstallCommand extends BaseInitCommand
{
    use ExecutesShellCommands;

    protected $signature = 'init:install {--force : Overwrite any existing files} {--dev : Install development versions of packages}';

    protected $description = 'Install all Fuelviews packages, TailwindCSS, Vite, Prettier, and other dependencies';

    public function handle(): int
    {
        $this->info('🚀 Starting the complete Laravel Init installation process...');
        $this->newLine();


        $commands = [
            'init:changelog' => 'Setting up changelog',
            'init:vite' => 'Installing Vite',
            'init:tailwindcss' => 'Installing TailwindCSS',
            'init:prettier' => 'Installing Prettier',
            'init:git-dot-files' => 'Setting up Git files',
            'init:composer-packages' => 'Installing Composer packages',
        ];

        $failed = [];
        $options = $this->getCommandOptions();

        foreach ($commands as $command => $description) {
            $this->startTask($description);
            

            $exitCode = $this->call($command, $options);
            
            if ($exitCode === 0) {
                $this->completeTask($description);
            } else {
                $this->failTask($description);
                $failed[] = $command;
            }
        }

        if (! empty($failed)) {
            $this->error('Some commands failed:');
            foreach ($failed as $command) {
                $this->line("  • $command");
            }

            return self::FAILURE;
        }

        // Post-installation tasks
        $this->newLine();
        $this->info('Running post-installation tasks...');

        // Run migrations
        $this->startTask('Running database migrations');
        $migrationsSuccess = $this->runArtisanCommand('migrate', $options);
        
        if ($migrationsSuccess) {
            $this->completeTask('Database migrations completed');
        } else {
            $this->warn('Database migrations failed (this may be expected if no migrations are pending)');
        }

        // Clear caches
        $this->startTask('Clearing application caches');
        $this->runArtisanCommand('optimize:clear');
        $this->completeTask('Application caches cleared');

        // Build frontend assets
        $this->startTask('Building frontend assets');
        $buildSuccess = $this->runShellCommand('npm run build');
        
        if ($buildSuccess) {
            $this->completeTask('Frontend assets built successfully');
        } else {
            $this->warn('Frontend build failed (make sure Node.js is installed)');
        }

        $this->newLine();
        $this->info('✅ Laravel Init installation completed successfully!');
        $this->newLine();
        
        $this->info('Next steps:');
        $this->line('  • Run "php artisan init:status" to check installation status');
        $this->line('  • Run "npm run dev" to start the development server');
        $this->line('  • Check your .env file for any additional configuration');
        
        return self::SUCCESS;
    }

    private function getCommandOptions(): array
    {
        $options = [];
        
        if ($this->isForce()) {
            $options['--force'] = true;
        }
        
        
        if ($this->isDev()) {
            $options['--dev'] = true;
        }
        
        return $options;
    }
}
