<?php

use Fuelviews\Init\Commands\InstallViteCommand;
use Fuelviews\Init\Traits\ExecutesShellCommands;

beforeEach(function () {
    // Use InstallViteCommand as it uses the ExecutesShellCommands trait
    $this->command = new InstallViteCommand();
});

it('can check if command exists', function () {
    $command = Mockery::mock(InstallViteCommand::class)->makePartial();
    
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('commandExists');
    $method->setAccessible(true);
    
    // Test with a command that should exist on most systems
    $exists = $method->invoke($command, 'php');
    expect($exists)->toBeTrue();
    
    // Test with a command that shouldn't exist
    $exists = $method->invoke($command, 'non-existent-command-12345');
    expect($exists)->toBeFalse();
});

it('builds composer command correctly in dev mode', function () {
    $command = Mockery::mock(InstallViteCommand::class)->makePartial();
    $command->shouldReceive('isDev')->andReturn(true);
    $command->shouldReceive('output->isQuiet')->andReturn(false);
    $command->shouldReceive('output->isVerbose')->andReturn(false);
    $command->shouldReceive('runShellCommand')
        ->with('composer require test-package --prefer-source', 600)
        ->andReturn(true);
    
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('runComposerCommand');
    $method->setAccessible(true);
    
    $result = $method->invoke($command, 'require test-package');
    expect($result)->toBeTrue();
});

it('builds composer command correctly in normal mode', function () {
    $command = Mockery::mock(InstallViteCommand::class)->makePartial();
    $command->shouldReceive('isDev')->andReturn(false);
    $command->shouldReceive('output->isQuiet')->andReturn(false);
    $command->shouldReceive('output->isVerbose')->andReturn(false);
    $command->shouldReceive('runShellCommand')
        ->with('composer require test-package', 600)
        ->andReturn(true);
    
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('runComposerCommand');
    $method->setAccessible(true);
    
    $result = $method->invoke($command, 'require test-package');
    expect($result)->toBeTrue();
});

it('adds quiet flag to composer command when output is quiet', function () {
    $command = Mockery::mock(InstallViteCommand::class)->makePartial();
    $command->shouldReceive('isDev')->andReturn(false);
    $command->shouldReceive('output->isQuiet')->andReturn(true);
    $command->shouldReceive('output->isVerbose')->andReturn(false);
    $command->shouldReceive('runShellCommand')
        ->with('composer require test-package --quiet', 600)
        ->andReturn(true);
    
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('runComposerCommand');
    $method->setAccessible(true);
    
    $result = $method->invoke($command, 'require test-package');
    expect($result)->toBeTrue();
});

it('adds verbose flag to composer command when output is verbose', function () {
    $command = Mockery::mock(InstallViteCommand::class)->makePartial();
    $command->shouldReceive('isDev')->andReturn(false);
    $command->shouldReceive('output->isQuiet')->andReturn(false);
    $command->shouldReceive('output->isVerbose')->andReturn(true);
    $command->shouldReceive('runShellCommand')
        ->with('composer require test-package -vvv', 600)
        ->andReturn(true);
    
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('runComposerCommand');
    $method->setAccessible(true);
    
    $result = $method->invoke($command, 'require test-package');
    expect($result)->toBeTrue();
});