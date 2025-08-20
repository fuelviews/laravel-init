<?php

use Fuelviews\Init\Commands\BaseInitCommand;
use Fuelviews\Init\Commands\InstallViteCommand;

beforeEach(function () {
    // Use InstallViteCommand as a concrete implementation of BaseInitCommand for testing
    $this->command = new InstallViteCommand();
});

it('can check if force option is enabled', function () {
    $command = Mockery::mock(BaseInitCommand::class)->makePartial();
    $command->shouldReceive('option')
        ->with('force')
        ->andReturn(true);
    
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('isForce');
    $method->setAccessible(true);
    
    expect($method->invoke($command))->toBeTrue();
});

it('can check if dev option is enabled', function () {
    $command = Mockery::mock(BaseInitCommand::class)->makePartial();
    $command->shouldReceive('option')
        ->with('dev')
        ->andReturn(true);
    
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('isDev');
    $method->setAccessible(true);
    
    expect($method->invoke($command))->toBeTrue();
});

it('returns false for dev option when not provided', function () {
    $command = Mockery::mock(BaseInitCommand::class)->makePartial();
    $command->shouldReceive('option')
        ->with('dev')
        ->andReturn(null);
    
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('isDev');
    $method->setAccessible(true);
    
    expect($method->invoke($command))->toBeFalse();
});

it('can get stub path correctly', function () {
    $command = Mockery::mock(BaseInitCommand::class)->makePartial();
    
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('getStubPath');
    $method->setAccessible(true);
    
    $path = $method->invoke($command, 'test-file');
    
    expect($path)
        ->toContain('stubs/test-file.stub')
        ->and(str_ends_with($path, 'test-file.stub'))
        ->toBeTrue();
});

it('automatically adds stub extension when not provided', function () {
    $command = Mockery::mock(BaseInitCommand::class)->makePartial();
    
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('getStubPath');
    $method->setAccessible(true);
    
    $path = $method->invoke($command, 'test-file');
    
    expect($path)->toEndWith('.stub');
});

it('does not double add stub extension', function () {
    $command = Mockery::mock(BaseInitCommand::class)->makePartial();
    
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('getStubPath');
    $method->setAccessible(true);
    
    $path = $method->invoke($command, 'test-file.stub');
    
    expect($path)
        ->toEndWith('.stub')
        ->and($path)
        ->not->toContain('.stub.stub');
});