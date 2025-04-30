<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim4\Root\PathsInterface;
use Slim4\Root\PathsProvider;

require_once __DIR__ . '/../../vendor/autoload.php';

// Define root path
$rootPath = dirname(__DIR__);

// Create container
$containerBuilder = new ContainerBuilder();
$container = $containerBuilder->build();

// Register paths services with auto-discovery and validation
PathsProvider::register($container, $rootPath, [], true, true);

// Create app
$app = AppFactory::createFromContainer($container);

// Add middleware
$app->addRoutingMiddleware();
$app->add($container->get(Slim4\Path\PathsMiddleware::class));
$app->addErrorMiddleware(true, true, true);

// Define routes
$app->get('/', function (Request $request, Response $response) use ($container) {
    $paths = $request->getAttribute('paths');

    $html = sprintf(
        '<h1>Slim4 Path Example</h1>
        <p>Root path: %s</p>
        <p>Config path: %s</p>
        <p>Views path: %s</p>
        <p>Public path: %s</p>',
        $paths->getRootPath(),
        $paths->getConfigPath(),
        $paths->getViewsPath(),
        $paths->getPublicPath()
    );

    $response->getBody()->write($html);

    return $response;
});

$app->get('/paths', function (Request $request, Response $response) use ($container) {
    $paths = $container->get(PathsInterface::class);

    // Get all paths directly
    $data = $paths->getPaths();

    $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));

    return $response->withHeader('Content-Type', 'application/json');
});

// Route to demonstrate path discovery
$app->get('/discovery', function (Request $request, Response $response) use ($container) {
    $discoverer = $container->get(Slim4\Root\PathsDiscoverer::class);
    $rootPath = dirname(__DIR__);

    $discoveredPaths = $discoverer->discover($rootPath);

    $response->getBody()->write(json_encode([
        'rootPath' => $rootPath,
        'discoveredPaths' => $discoveredPaths
    ], JSON_PRETTY_PRINT));

    return $response->withHeader('Content-Type', 'application/json');
});

// Run app
$app->run();
