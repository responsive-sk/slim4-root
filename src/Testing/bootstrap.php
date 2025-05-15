<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Slim4\Root\Paths;
use Slim4\Root\Testing\TestContainer;

require_once __DIR__ . '/../../../../vendor/autoload.php';

// Load .env file if it exists
if (file_exists(__DIR__ . '/../../../../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../../../');
    $dotenv->load();
}

// Create Paths object for tests
$rootPath = (string)realpath(__DIR__ . '/../../../../');
$paths = new Paths($rootPath);

// Store Paths in TestContainer
TestContainer::set(Paths::class, $paths);

// Create container
$containerBuilder = new ContainerBuilder();

// Add Paths to container
$containerBuilder->addDefinitions([
    Paths::class => $paths,
]);

// Load dependencies
if (file_exists(__DIR__ . '/../../../../config/container/dependencies.php')) {
    /** @var array<string, mixed> $dependencies */
    $dependencies = require __DIR__ . '/../../../../config/container/dependencies.php';
    $containerBuilder->addDefinitions($dependencies);
} elseif (file_exists(__DIR__ . '/../../../../config/dependencies.php')) {
    /** @var array<string, mixed> $dependencies */
    $dependencies = require __DIR__ . '/../../../../config/dependencies.php';
    $containerBuilder->addDefinitions($dependencies);
}

// Build container
$container = $containerBuilder->build();

// Store container in TestContainer
TestContainer::set('container', $container);

// We won't create the application here to avoid session issues
// Each test should create its own application instance if needed
