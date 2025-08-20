<?php

use Fuelviews\Init\Init;

beforeEach(function () {
    $this->init = new Init();
});

it('can get package info from composer.json', function () {
    $packageInfo = $this->init->getPackageInfo();
    
    expect($packageInfo)
        ->toBeArray()
        ->and($packageInfo)
        ->toHaveKey('name')
        ->and($packageInfo['name'])
        ->toBe('fuelviews/laravel-init');
});

it('returns version from package info', function () {
    $version = $this->init->getVersion();
    
    expect($version)->toBeString();
});

it('returns dev-main as default version when not specified', function () {
    // Mock getPackageInfo to return array without version
    $init = Mockery::mock(Init::class)->makePartial();
    $init->shouldReceive('getPackageInfo')->andReturn([]);
    
    expect($init->getVersion())->toBe('dev-main');
});

it('can get available commands', function () {
    $commands = $this->init->getAvailableCommands();
    
    expect($commands)
        ->toBeArray()
        ->and($commands)
        ->toHaveKey('init:install')
        ->and($commands['init:install'])
        ->toBe('Install all Fuelviews packages and dependencies')
        ->and($commands)
        ->toHaveKey('init:composer-packages')
        ->and($commands)
        ->toHaveKey('init:vite')
        ->and($commands)
        ->toHaveKey('init:tailwindcss');
});

it('can check if stub exists', function () {
    // Test with a stub that should exist
    $exists = $this->init->hasStub('vite.config.js');
    
    expect($exists)->toBeTrue();
});

it('returns false for non-existent stub', function () {
    $exists = $this->init->hasStub('non-existent-file');
    
    expect($exists)->toBeFalse();
});

it('can get stub path', function () {
    $path = $this->init->getStubPath('vite.config.js');
    
    expect($path)
        ->toContain('stubs/vite.config.js.stub')
        ->and(str_ends_with($path, 'vite.config.js.stub'))
        ->toBeTrue();
});

it('can get publishing tags', function () {
    $tags = $this->init->getPublishingTags();
    
    expect($tags)
        ->toBeArray()
        ->and($tags)
        ->toHaveKey('init-all')
        ->and($tags)
        ->toHaveKey('init-stubs')
        ->and($tags)
        ->toHaveKey('init-vite')
        ->and($tags)
        ->toHaveKey('init-tailwind');
});

it('can get configuration status', function () {
    $status = $this->init->getConfigurationStatus();
    
    expect($status)
        ->toBeArray()
        ->and($status)
        ->toHaveKey('vite.config.js')
        ->and($status['vite.config.js'])
        ->toHaveKey('description')
        ->and($status['vite.config.js'])
        ->toHaveKey('exists')
        ->and($status['vite.config.js'])
        ->toHaveKey('path');
});
