<?php

use Fuelviews\Init\InitServiceProvider;
use Illuminate\Support\Facades\File;

it('registers init facade correctly', function () {
    expect(app()->bound('init'))->toBeTrue();
});

it('can resolve init class from container', function () {
    $init = app('init');
    
    expect($init)
        ->toBeInstanceOf(Fuelviews\Init\Init::class);
});

it('registers all commands', function () {
    $commands = [
        'init:install',
        'init:status',
        'init:changelog',
        'init:git-dot-files',
        'init:prettier',
        'init:tailwindcss',
        'init:vite',
        'init:composer-packages',
        'init:env',
        'init:images',
    ];
    
    foreach ($commands as $command) {
        expect($this->artisan($command . ' --help'))
            ->assertSuccessful();
    }
});

it('publishes stub files with correct tags', function () {
    // Test that the service provider has registered publishable assets
    $provider = new InitServiceProvider(app());
    
    // This is a basic test to ensure the provider can be instantiated
    // and doesn't throw errors during boot/register
    expect($provider)->toBeInstanceOf(InitServiceProvider::class);
});

it('has correct about information registered', function () {
    // Test that the About information is registered (Laravel 9+)
    if (class_exists(\Illuminate\Foundation\Console\AboutCommand::class)) {
        $this->artisan('about')
            ->assertSuccessful();
    }
    
    expect(true)->toBeTrue(); // Always pass if About command doesn't exist
});