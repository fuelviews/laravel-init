<?php

use Fuelviews\Init\Commands\InstallComposerPackagesCommand;

beforeEach(function () {
    $this->command = new InstallComposerPackagesCommand();
});

it('has correct signature', function () {
    expect($this->command->signature)
        ->toBe('init:composer-packages {--force : Overwrite any existing files} {--dev : Install development versions of packages}');
});

it('has correct description', function () {
    expect($this->command->description)
        ->toBe('Install Composer packages for Fuelviews and Laravel projects');
});

it('returns correct stable package versions by default', function () {
    $command = Mockery::mock(InstallComposerPackagesCommand::class)->makePartial();
    $command->shouldReceive('isDev')->andReturn(false);
    
    // Use reflection to test the private getPackageVersions method
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('getPackageVersions');
    $method->setAccessible(true);
    
    $versions = $method->invoke($command);
    
    expect($versions)
        ->toEqual([
            'fuelviews/laravel-sabhero-wrapper' => '^0.0',
            'fuelviews/laravel-cloudflare-cache' => '^1.0',
            'fuelviews/laravel-robots-txt' => '^0.0',
            'fuelviews/laravel-sitemap' => '^0.0',
        ]);
});

it('returns dev versions for fuelviews packages only when dev flag is used', function () {
    $command = Mockery::mock(InstallComposerPackagesCommand::class)->makePartial();
    $command->shouldReceive('isDev')->andReturn(true);
    
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('getPackageVersions');
    $method->setAccessible(true);
    
    $versions = $method->invoke($command);
    
    expect($versions)
        ->toEqual([
            'fuelviews/laravel-sabhero-wrapper' => 'dev-main',
            'fuelviews/laravel-cloudflare-cache' => 'dev-main',
            'fuelviews/laravel-robots-txt' => 'dev-main',
            'fuelviews/laravel-sitemap' => 'dev-main',
        ]);
});

it('can be called via artisan', function () {
    $this->artisan('init:composer-packages')
        ->expectsOutputToContain('Installing Composer packages for Laravel')
        ->assertSuccessful();
});