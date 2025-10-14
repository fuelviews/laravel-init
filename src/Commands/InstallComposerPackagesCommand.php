<?php

namespace Fuelviews\Init\Commands;

use Fuelviews\Init\Traits\ExecutesShellCommands;
use Illuminate\Support\Facades\File;

class InstallComposerPackagesCommand extends BaseInitCommand
{
    use ExecutesShellCommands;

    protected $signature = 'init:composer-packages {--force : Overwrite any existing files} {--dev : Install development versions of packages}';

    protected $description = 'Install Composer packages for Fuelviews and Laravel projects';

    public function handle(): int
    {
        $this->info('Installing Composer packages for Laravel...');


        $this->startTask('Installing Composer packages');
        $success = $this->installComposerPackages();

        if ($success) {
            $this->completeTask('Composer packages installed');
            $this->info('✅ Composer packages installation completed successfully!');

            return self::SUCCESS;
        } else {
            $this->failTask('Failed to install some packages');

            return self::FAILURE;
        }
    }

    private function installComposerPackages(): bool
    {
        // Get package versions based on dev flag
        $packageVersions = $this->getPackageVersions();

        // Packages with version constraints
        $packagesWithVersions = [
            'fuelviews/laravel-sabhero-wrapper:"' . $packageVersions['fuelviews/laravel-sabhero-wrapper'] . '"',
            'fuelviews/laravel-cloudflare-cache:"' . $packageVersions['fuelviews/laravel-cloudflare-cache'] . '"',
            'fuelviews/laravel-robots-txt:"' . $packageVersions['fuelviews/laravel-robots-txt'] . '"',
            'fuelviews/laravel-sitemap:"' . $packageVersions['fuelviews/laravel-sitemap'] . '"',
        ];

        // Packages without version constraints - these don't use dev versions
        $packagesWithoutVersions = [
            'ralphjsmit/laravel-seo',
            'ralphjsmit/laravel-glide',
            'livewire/livewire',
            'spatie/laravel-google-fonts',
            'spatie/laravel-googletagmanager',
        ];

        $packagesDev = [
            'spatie/image-optimizer',
        ];

        $allSuccess = true;

        // Install packages with version constraints as a group
        if (! empty($packagesWithVersions)) {
            $this->info('Installing packages with version constraints...');
            $requireCommand = 'require ' . implode(' ', $packagesWithVersions);
            if (! $this->runComposerCommand($requireCommand)) {
                $allSuccess = false;
            }
        }

        // Install packages without version constraints as a group
        if (! empty($packagesWithoutVersions)) {
            $this->info('Installing packages without version constraints...');
            $requireCommand = 'require ' . implode(' ', $packagesWithoutVersions);
            if (! $this->runComposerCommand($requireCommand)) {
                $allSuccess = false;
            }
        }

        // Install dev packages
        if (! empty($packagesDev)) {
            $this->info('Installing dev packages...');
            $requireCommandDev = 'require --dev ' . implode(' ', $packagesDev);
            if (! $this->runComposerCommand($requireCommandDev)) {
                $allSuccess = false;
            }
        }

        // Run package-specific install commands
        if ($allSuccess) {
            $allSuccess = $this->runPackageInstallCommands();
        }

        // Run composer dump-autoload to update autoload files after publishing
        if ($allSuccess) {
            $this->info('Updating composer autoload files...');
            $this->runComposerCommand('dump-autoload --optimize');
        }

        // Update routes file
        if ($allSuccess) {
            $this->updateWelcomeRoute();
        }

        return $allSuccess;
    }

    private function getPackageVersions(): array
    {
        if ($this->isDev()) {
            // Development versions - only apply to fuelviews packages
            return [
                'fuelviews/laravel-sabhero-wrapper' => 'dev-main',
                'fuelviews/laravel-cloudflare-cache' => 'dev-main',
                'fuelviews/laravel-robots-txt' => 'dev-main',
                'fuelviews/laravel-sitemap' => 'dev-main',
            ];
        }

        // Default stable versions
        return [
            'fuelviews/laravel-sabhero-wrapper' => '^1.0',
            'fuelviews/laravel-cloudflare-cache' => '^1.0',
            'fuelviews/laravel-robots-txt' => '^1.0',
            'fuelviews/laravel-sitemap' => '^1.0',
        ];
    }

    private function runPackageInstallCommands(): bool
    {
        $force = $this->isForce() ? ['--force' => true] : [];
        $allSuccess = true;

        $publishCommands = [
            'cloudflare-cache-config' => 'Publishing Cloudflare cache config',
            'sitemap-config' => 'Publishing sitemap config',
            'robots-txt-config' => 'Publishing robots.txt config',
            'sabhero-wrapper-welcome' => 'Publishing Sabhero wrapper welcome',
            'seo-migrations' => 'Publishing SEO migrations',
            'seo-config' => 'Publishing SEO config',
            'google-fonts-config' => 'Publishing Google Fonts config',
            'googletagmanager' => 'Publishing Google Tag Manager config',
        ];

        foreach ($publishCommands as $tag => $description) {
            $this->startTask($description);

            // Google Tag Manager uses generic 'config' tag, so we need provider-based publishing
            if ($tag === 'googletagmanager') {
                $success = $this->runArtisanCommand('vendor:publish', array_merge([
                    '--provider' => 'Spatie\GoogleTagManager\GoogleTagManagerServiceProvider',
                    '--tag' => 'config',
                ], $force));
            } else {
                $success = $this->runArtisanCommand('vendor:publish', array_merge(['--tag' => $tag], $force));
            }

            if ($success) {
                $this->completeTask($description);
            } else {
                $this->failTask($description);
                $allSuccess = false;
            }
        }

        // Install sabhero-wrapper config and migrations
        $this->startTask('Installing Sabhero wrapper');
        $configSuccess = $this->runArtisanCommand('vendor:publish', ['--tag' => 'sabhero-wrapper-config', '--force' => true]);
        $migrationSuccess = $this->runArtisanCommand('vendor:publish', ['--tag' => 'sabhero-wrapper-migrations', '--force' => true]);

        if ($configSuccess && $migrationSuccess) {
            $this->completeTask('Sabhero wrapper installed');
            
            // Publish welcome.blade.php from sabhero-wrapper if force flag is set
            if ($this->isForce()) {
                $this->startTask('Publishing Sabhero wrapper welcome view');
                $publishSuccess = $this->runArtisanCommand('vendor:publish', [
                    '--tag' => 'init-sabhero-welcome',
                    '--force' => true,
                ]);
                
                if ($publishSuccess) {
                    $this->completeTask('Sabhero wrapper welcome view published');
                } else {
                    $this->failTask('Failed to publish Sabhero wrapper welcome view');
                    // Don't fail the entire process for this optional step
                }
            }
        } else {
            $this->failTask('Sabhero wrapper installation failed');
            $allSuccess = false;
        }

        // Ensure storage link exists
        if (! $this->isStorageLinked()) {
            $this->startTask('Creating storage link');
            $success = $this->ensureStorageLink();

            if ($success) {
                $this->completeTask('Storage link created');
            } else {
                $this->failTask('Storage link creation failed');
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    private function updateWelcomeRoute(): void
    {

        $filePath = base_path('routes/web.php');

        if (! File::exists($filePath)) {
            $this->warn('routes/web.php not found, skipping route update');

            return;
        }

        $search = "Route::get('/', function () {\n    return view('welcome');\n});";
        $replace = "Route::get('/', function () {\n    return view('welcome');\n})->name('welcome');";

        $fileContents = File::get($filePath);
        $updatedContents = str_replace($search, $replace, $fileContents);

        if ($fileContents !== $updatedContents) {
            File::put($filePath, $updatedContents);
            $this->info('Updated welcome route with name');
        }
    }
}
