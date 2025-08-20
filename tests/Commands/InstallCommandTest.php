<?php

use Fuelviews\Init\Commands\InstallCommand;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    $this->command = new InstallCommand();
});

it('has correct signature', function () {
    expect($this->command->signature)
        ->toBe('init:install {--force : Overwrite any existing files} {--dev : Install development versions of packages}');
});

it('has correct description', function () {
    expect($this->command->description)
        ->toBe('Install all Fuelviews packages, TailwindCSS, Vite, Prettier, and other dependencies');
});

it('can be called via artisan', function () {
    // Mock the sub-commands to prevent actual execution during test
    Artisan::shouldReceive('call')
        ->with('init:changelog', Mockery::any())
        ->andReturn(0);
    
    Artisan::shouldReceive('call')
        ->with('init:vite', Mockery::any())
        ->andReturn(0);
        
    Artisan::shouldReceive('call')
        ->with('init:tailwindcss', Mockery::any())
        ->andReturn(0);
        
    Artisan::shouldReceive('call')
        ->with('init:prettier', Mockery::any())
        ->andReturn(0);
        
    Artisan::shouldReceive('call')
        ->with('init:git-dot-files', Mockery::any())
        ->andReturn(0);
        
    Artisan::shouldReceive('call')
        ->with('init:composer-packages', Mockery::any())
        ->andReturn(0);
    
    Artisan::shouldReceive('call')
        ->with('migrate', Mockery::any())
        ->andReturn(0);
    
    Artisan::shouldReceive('call')
        ->with('optimize:clear')
        ->andReturn(0);

    $this->artisan('init:install')
        ->expectsOutputToContain('🚀 Starting the complete Laravel Init installation process')
        ->expectsOutputToContain('✅ Laravel Init installation completed successfully!')
        ->assertSuccessful();
});

it('passes dev flag to sub-commands when provided', function () {
    // We can test this by checking the options array that gets passed
    $command = Mockery::mock(InstallCommand::class)->makePartial();
    $command->shouldReceive('option')
        ->with('dev')
        ->andReturn(true);
    $command->shouldReceive('option')
        ->with('force')
        ->andReturn(false);
    
    // Use reflection to test the private getCommandOptions method
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('getCommandOptions');
    $method->setAccessible(true);
    
    $options = $method->invoke($command);
    
    expect($options)
        ->toHaveKey('--dev')
        ->and($options['--dev'])
        ->toBeTrue();
});

it('passes force flag to sub-commands when provided', function () {
    $command = Mockery::mock(InstallCommand::class)->makePartial();
    $command->shouldReceive('option')
        ->with('force')
        ->andReturn(true);
    $command->shouldReceive('option')
        ->with('dev')
        ->andReturn(false);
    
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('getCommandOptions');
    $method->setAccessible(true);
    
    $options = $method->invoke($command);
    
    expect($options)
        ->toHaveKey('--force')
        ->and($options['--force'])
        ->toBeTrue();
});