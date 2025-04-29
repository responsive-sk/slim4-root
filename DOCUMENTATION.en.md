# Slim4 Root - Documentation

## Table of Contents

1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Basic Usage](#basic-usage)
4. [Configuration](#configuration)
5. [Auto-discovery](#auto-discovery)
6. [Path Validation](#path-validation)
7. [Path Normalization](#path-normalization)
8. [DI Container Integration](#di-container-integration)
9. [Middleware](#middleware)
10. [Twig Integration](#twig-integration)
11. [Monolog Integration](#monolog-integration)
12. [Available Methods](#available-methods)
13. [Usage Examples](#usage-examples)
14. [Troubleshooting](#troubleshooting)
15. [Contributing](#contributing)

## Introduction

Slim4 Root is a package for the Slim 4 framework that provides centralized path management relative to the project's root directory. The package solves the problem of relative paths (`../../../`) in code and provides a unified way to access directories and files in the project.

### Key Features

- **Centralized path management** - All paths are defined in one place
- **Automatic directory structure discovery** - The package can automatically discover common directory structures
- **Path validation** - The package can validate if paths exist
- **Path normalization** - The package can normalize paths (e.g., remove trailing slashes, replace backslashes with forward slashes)
- **Middleware** - The package provides middleware for accessing paths in route handlers
- **DI container integration** - The package provides a provider for registering services in the DI container

## Installation

You can install the package via Composer:

```bash
composer require responsive-sk/slim4-root
```

## Basic Usage

```php
use Slim4\Root\Paths;

// Create a Paths instance with auto-discovery enabled
$paths = new Paths(__DIR__);

// Get paths relative to the root
$configPath = $paths->getConfigPath();
$viewsPath = $paths->getViewsPath();
$logsPath = $paths->getLogsPath();

// Get all paths at once
$allPaths = $paths->getPaths();

// No more ../../../ paths!
// Instead of:
// require_once __DIR__ . '/../../../vendor/autoload.php';
// Use:
// require_once $paths->path('vendor/autoload.php');
```

## Configuration

You can configure the package with custom paths:

```php
use Slim4\Root\Paths;

// Create a Paths instance with custom paths
$paths = new Paths(
    __DIR__,
    [
        'config' => __DIR__ . '/app/config',
        'views' => __DIR__ . '/app/views',
        'logs' => __DIR__ . '/app/logs',
    ],
    true, // Enable auto-discovery (default: true)
    false // Disable path validation (default: false)
);

// Get paths
$configPath = $paths->getConfigPath(); // Returns __DIR__ . '/app/config'
$viewsPath = $paths->getViewsPath(); // Returns __DIR__ . '/app/views'
$logsPath = $paths->getLogsPath(); // Returns __DIR__ . '/app/logs'
```

## Auto-discovery

The `Paths` class can automatically discover common directory structures in your project. This feature is enabled by default, but you can disable it by passing `false` as the third parameter to the constructor.

```php
use Slim4\Root\Paths;

// With auto-discovery enabled (default)
$paths = new Paths(__DIR__);

// Without auto-discovery
$paths = new Paths(__DIR__, [], false);
```

The auto-discovery process looks for the following directories:

- `config` - Looks for `config`, `app/config`, `etc`
- `resources` - Looks for `resources`, `app/resources`, `res`
- `views` - Looks for `resources/views`, `templates`, `views`, `app/views`
- `assets` - Looks for `resources/assets`, `assets`, `public/assets`
- `cache` - Looks for `var/cache`, `cache`, `tmp/cache`, `storage/cache`
- `logs` - Looks for `var/logs`, `logs`, `log`, `storage/logs`
- `public` - Looks for `public`, `web`, `www`, `htdocs`
- `database` - Looks for `database`, `db`, `storage/database`
- `migrations` - Looks for `database/migrations`, `migrations`, `db/migrations`
- `storage` - Looks for `storage`, `var`, `data`
- `tests` - Looks for `tests`, `test`

You can use the `PathsDiscoverer` class directly:

```php
use Slim4\Root\PathsDiscoverer;

// Create a PathsDiscoverer instance
$discoverer = new PathsDiscoverer();

// Discover paths
$discoveredPaths = $discoverer->discover(__DIR__);

// Use discovered paths
var_dump($discoveredPaths);
```

## Path Validation

The `Paths` class can validate if paths exist. This feature is disabled by default, but you can enable it by passing `true` as the fourth parameter to the constructor.

```php
use Slim4\Root\Paths;

// Without validation (default)
$paths = new Paths(__DIR__);

// With validation
$paths = new Paths(__DIR__, [], true, true);
```

If validation is enabled and a path doesn't exist, an `InvalidPathException` will be thrown:

```php
use Slim4\Root\Paths;
use Slim4\Root\Exception\InvalidPathException;

try {
    $paths = new Paths(__DIR__, [], true, true);
} catch (InvalidPathException $e) {
    echo $e->getMessage(); // "Configured path for 'views' is not a valid directory: /path/to/views"
}
```

You can use the `PathsValidator` class directly:

```php
use Slim4\Root\PathsValidator;
use Slim4\Root\Exception\InvalidPathException;

// Create a PathsValidator instance
$validator = new PathsValidator();

// Validate paths
try {
    $validator->validate([
        'config' => __DIR__ . '/config',
        'views' => __DIR__ . '/views',
    ], true); // Strict validation (throws exception if path doesn't exist)
} catch (InvalidPathException $e) {
    echo $e->getMessage();
}
```

## Path Normalization

The package provides a `PathsNormalizer` class for normalizing paths:

```php
use Slim4\Root\PathsNormalizer;

// Create a PathsNormalizer instance
$normalizer = new PathsNormalizer();

// Normalize paths
$normalizedPath = $normalizer->normalize('/var/www/project/');
// Returns: '/var/www/project'

// Normalize Windows paths (if working on Windows)
$windowsPath = $normalizer->normalize('D:\\projects\\slim4\\');
// Returns: 'D:/projects/slim4'
```

## DI Container Integration

The package provides a `PathsProvider` class for registering services in the DI container:

```php
use Slim4\Root\PathsProvider;
use DI\ContainerBuilder;

// Create container
$containerBuilder = new ContainerBuilder();

// Register paths services
$rootPath = dirname(__DIR__);
PathsProvider::register(
    $containerBuilder->build(),
    $rootPath,
    [], // Custom paths
    true, // Enable auto-discovery
    false // Disable path validation
);

// Get paths from the container
$paths = $container->get(Slim4\Root\PathsInterface::class);
```

## Middleware

The package provides a `PathsMiddleware` for accessing paths in route handlers:

```php
use Slim4\Root\PathsMiddleware;
use Slim4\Root\PathsInterface;

// Add middleware
$app->add(new PathsMiddleware($paths));

// Use paths in route handlers
$app->get('/', function (Request $request, Response $response) {
    // Get paths from request attributes
    $paths = $request->getAttribute(PathsInterface::class);

    // Use paths
    $configPath = $paths->getConfigPath();

    // ...

    return $response;
});
```

## Twig Integration

You can integrate the package with Twig:

```php
use Slim4\Root\PathsInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Create Twig environment
$paths = $container->get(PathsInterface::class);
$loader = new FilesystemLoader($paths->getViewsPath());
$twig = new Environment($loader, [
    'cache' => $paths->getCachePath() . '/twig',
    'auto_reload' => true,
]);

// Add paths to Twig global variables
$twig->addGlobal('paths', $paths);
```

In Twig templates:

```twig
{# Use paths in templates #}
<link rel="stylesheet" href="{{ paths.getPublicPath() }}/css/style.css">
<script src="{{ paths.getPublicPath() }}/js/app.js"></script>
```

## Monolog Integration

You can integrate the package with Monolog:

```php
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim4\Root\PathsInterface;

// Create logger
$container->set(Logger::class, function (ContainerInterface $container) {
    $paths = $container->get(PathsInterface::class);
    $logger = new Logger('app');
    $logger->pushHandler(new StreamHandler(
        $paths->getLogsPath() . '/app.log',
        Logger::DEBUG
    ));

    return $logger;
});
```

## Available Methods

### PathsInterface

- `getRootPath()` - Get the root path of the project
- `getConfigPath()` - Get the config path
- `getResourcesPath()` - Get the resources path
- `getViewsPath()` - Get the views path
- `getAssetsPath()` - Get the assets path
- `getCachePath()` - Get the cache path
- `getLogsPath()` - Get the logs path
- `getPublicPath()` - Get the public path
- `getDatabasePath()` - Get the database path
- `getMigrationsPath()` - Get the migrations path
- `getStoragePath()` - Get the storage path
- `getTestsPath()` - Get the tests path
- `path(string $path)` - Get a path relative to the root path
- `getPaths()` - Get all paths as an associative array

### PathsDiscoverer

- `discover(string $rootPath)` - Discover paths in the given root path

### PathsValidator

- `validate(array $paths, bool $strict)` - Validate paths

### PathsNormalizer

- `normalize(string $path)` - Normalize path

## Usage Examples

### Using with Slim 4 Application

```php
<?php

use Slim\Factory\AppFactory;
use Slim4\Root\Paths;
use Slim4\Root\PathsMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

// Create Paths instance
$rootPath = dirname(__DIR__);
$paths = new Paths($rootPath);

// Create application
$app = AppFactory::create();

// Add middleware
$app->add(new PathsMiddleware($paths));

// Define route
$app->get('/', function ($request, $response) {
    $paths = $request->getAttribute(Slim4\Root\PathsInterface::class);
    $response->getBody()->write('Root path: ' . $paths->getRootPath());
    return $response;
});

// Run application
$app->run();
```

### Using with Custom Directory Structure

```php
<?php

use Slim4\Root\Paths;

// Custom directory structure
$paths = new Paths(
    __DIR__,
    [
        'config' => __DIR__ . '/app/config',
        'views' => __DIR__ . '/app/views',
        'logs' => __DIR__ . '/app/logs',
        'cache' => __DIR__ . '/app/cache',
        'public' => __DIR__ . '/public',
        'database' => __DIR__ . '/app/database',
        'migrations' => __DIR__ . '/app/database/migrations',
        'storage' => __DIR__ . '/app/storage',
        'tests' => __DIR__ . '/tests',
    ]
);

// Use paths
$configPath = $paths->getConfigPath();
$viewsPath = $paths->getViewsPath();
$logsPath = $paths->getLogsPath();
```

### Using with PHP-DI

```php
<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim4\Root\PathsProvider;

// Create container
$containerBuilder = new ContainerBuilder();
$container = $containerBuilder->build();

// Register paths services
$rootPath = dirname(__DIR__);
PathsProvider::register($container, $rootPath);

// Create application
AppFactory::setContainer($container);
$app = AppFactory::create();

// Use paths from container
$paths = $container->get(Slim4\Root\PathsInterface::class);
```

## Integration with Hexagonal Architecture and DDD

The `slim4/root` package is ideal for projects using Hexagonal Architecture (HEXA) and Domain-Driven Design (DDD), which are increasingly popular in the PHP ecosystem.

### Benefits for Hexagonal Architecture

In hexagonal architecture (also known as Ports & Adapters), the application is divided into an inner domain and outer adapters. The `slim4/root` package helps:

1. **Clearly define project structure** - Enables consistent access to different parts of the application
2. **Separate domain from infrastructure** - Paths to domain, application, and infrastructure can be clearly defined
3. **Simplify testing** - Easier setup of test paths and fixtures

```php
<?php

use Slim4\Root\Paths;

// Create Paths instance for hexagonal architecture
$paths = new Paths(__DIR__, [
    // Base paths
    'domain' => __DIR__ . '/src/Domain',
    'application' => __DIR__ . '/src/Application',
    'infrastructure' => __DIR__ . '/src/Infrastructure',
    'interfaces' => __DIR__ . '/src/Interfaces',

    // Paths for ports and adapters
    'ports' => __DIR__ . '/src/Application/Ports',
    'adapters' => __DIR__ . '/src/Infrastructure/Adapters',

    // Paths for tests
    'tests.domain' => __DIR__ . '/tests/Domain',
    'tests.application' => __DIR__ . '/tests/Application',
    'tests.infrastructure' => __DIR__ . '/tests/Infrastructure',
]);

// Use paths in application
$domainPath = $paths->getPaths()['domain'];
$portsPath = $paths->getPaths()['ports'];
```

### Benefits for Domain-Driven Design (DDD)

For projects using DDD, the `slim4/root` package helps:

1. **Organize Bounded Contexts** - Clearly define paths to different bounded contexts
2. **Manage domain models** - Easier access to entities, value objects, and aggregates
3. **Support layered architecture** - Clearly separate domain, application, and infrastructure

```php
<?php

use Slim4\Root\Paths;

// Create Paths instance for DDD
$paths = new Paths(__DIR__, [
    // Bounded Contexts
    'contexts.user' => __DIR__ . '/src/Contexts/User',
    'contexts.product' => __DIR__ . '/src/Contexts/Product',
    'contexts.order' => __DIR__ . '/src/Contexts/Order',

    // Layers within context
    'contexts.user.domain' => __DIR__ . '/src/Contexts/User/Domain',
    'contexts.user.application' => __DIR__ . '/src/Contexts/User/Application',
    'contexts.user.infrastructure' => __DIR__ . '/src/Contexts/User/Infrastructure',

    // Domain components
    'contexts.user.domain.entities' => __DIR__ . '/src/Contexts/User/Domain/Entities',
    'contexts.user.domain.value-objects' => __DIR__ . '/src/Contexts/User/Domain/ValueObjects',
    'contexts.user.domain.repositories' => __DIR__ . '/src/Contexts/User/Domain/Repositories',
]);

// Use paths in application
$userContextPath = $paths->getPaths()['contexts.user'];
$userEntitiesPath = $paths->getPaths()['contexts.user.domain.entities'];
```

### Database Access for Each Port

One of the advantages of hexagonal architecture is the ability to have different implementations of ports for different databases. The `slim4/root` package helps you manage paths to these implementations:

```php
<?php

use Slim4\Root\Paths;

// Create Paths instance for different database implementations
$paths = new Paths(__DIR__, [
    // Base paths
    'domain' => __DIR__ . '/src/Domain',
    'application' => __DIR__ . '/src/Application',
    'infrastructure' => __DIR__ . '/src/Infrastructure',

    // Ports (interfaces)
    'ports.repositories' => __DIR__ . '/src/Application/Ports/Repositories',

    // Adapters for different databases
    'adapters.repositories.mysql' => __DIR__ . '/src/Infrastructure/Adapters/Repositories/MySQL',
    'adapters.repositories.pgsql' => __DIR__ . '/src/Infrastructure/Adapters/Repositories/PostgreSQL',
    'adapters.repositories.mongodb' => __DIR__ . '/src/Infrastructure/Adapters/Repositories/MongoDB',
    'adapters.repositories.redis' => __DIR__ . '/src/Infrastructure/Adapters/Repositories/Redis',

    // Configuration for different databases
    'config.mysql' => __DIR__ . '/config/databases/mysql.php',
    'config.pgsql' => __DIR__ . '/config/databases/pgsql.php',
    'config.mongodb' => __DIR__ . '/config/databases/mongodb.php',
    'config.redis' => __DIR__ . '/config/databases/redis.php',
]);

// Use in a repository factory
class RepositoryFactory
{
    private $paths;

    public function __construct(Slim4\Root\PathsInterface $paths)
    {
        $this->paths = $paths;
    }

    public function createUserRepository(string $driver = 'mysql')
    {
        // Get path to repository implementation based on driver
        $adapterPath = $this->paths->getPaths()['adapters.repositories.' . $driver];
        $configPath = $this->paths->getPaths()['config.' . $driver];

        // Load configuration
        $config = require $configPath;

        // Create and return the appropriate repository
        switch ($driver) {
            case 'mysql':
                return new \App\Infrastructure\Adapters\Repositories\MySQL\UserRepository($config);
            case 'pgsql':
                return new \App\Infrastructure\Adapters\Repositories\PostgreSQL\UserRepository($config);
            case 'mongodb':
                return new \App\Infrastructure\Adapters\Repositories\MongoDB\UserRepository($config);
            case 'redis':
                return new \App\Infrastructure\Adapters\Repositories\Redis\UserRepository($config);
            default:
                throw new \InvalidArgumentException("Unsupported driver: {$driver}");
        }
    }
}
```

This approach allows you to:

1. **Flexibility in database selection** - Easily switch between different databases without changing business logic
2. **Clean domain** - Domain doesn't need to know about the specific database
3. **Simple testing** - Easier creation of mock objects and test implementations
4. **Scalability** - Ability to use different databases for different parts of the application

## Advanced Usage

### Paths to Different Databases

One of the great advantages of the `slim4/root` package is the ability to easily access different databases and their configuration files. For example:

```php
<?php

use Slim4\Root\Paths;

// Create Paths instance
$paths = new Paths(__DIR__, [
    // Standard paths
    'database' => __DIR__ . '/database',

    // Paths to different databases
    'database.sqlite' => __DIR__ . '/database/sqlite',
    'database.mysql' => __DIR__ . '/database/mysql',
    'database.pgsql' => __DIR__ . '/database/pgsql',

    // Paths to migrations for different databases
    'migrations.sqlite' => __DIR__ . '/database/sqlite/migrations',
    'migrations.mysql' => __DIR__ . '/database/mysql/migrations',
    'migrations.pgsql' => __DIR__ . '/database/pgsql/migrations',

    // Paths to seeds for different databases
    'seeds.sqlite' => __DIR__ . '/database/sqlite/seeds',
    'seeds.mysql' => __DIR__ . '/database/mysql/seeds',
    'seeds.pgsql' => __DIR__ . '/database/pgsql/seeds',
]);

// Use paths to different databases
$sqlitePath = $paths->getPaths()['database.sqlite'];
$mysqlPath = $paths->getPaths()['database.mysql'];
$pgsqlPath = $paths->getPaths()['database.pgsql'];

// Use paths to migrations for different databases
$sqliteMigrationsPath = $paths->getPaths()['migrations.sqlite'];
$mysqlMigrationsPath = $paths->getPaths()['migrations.mysql'];
$pgsqlMigrationsPath = $paths->getPaths()['migrations.pgsql'];

// Access database configuration files
$sqliteConfigPath = $paths->path('config/database/sqlite.php');
$mysqlConfigPath = $paths->path('config/database/mysql.php');
$pgsqlConfigPath = $paths->path('config/database/pgsql.php');
```

This is particularly useful in projects that need to work with multiple databases simultaneously or support different database systems.

### Integration with Database Clients

The `slim4/root` package can be used to configure various database clients:

```php
<?php

use Slim4\Root\Paths;

// Create Paths instance
$paths = new Paths(__DIR__);

// SQLite
$sqliteConfig = [
    'driver' => 'sqlite',
    'database' => $paths->path('database/database.sqlite'),
];

// MySQL
$mysqlConfig = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'database',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'dump_path' => $paths->path('database/dumps/mysql'),
];

// PostgreSQL
$pgsqlConfig = [
    'driver' => 'pgsql',
    'host' => 'localhost',
    'database' => 'database',
    'username' => 'postgres',
    'password' => '',
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
    'dump_path' => $paths->path('database/dumps/pgsql'),
];

// Use with PDO
$sqlitePdo = new PDO('sqlite:' . $sqliteConfig['database']);

// Use with Doctrine DBAL
$connectionParams = [
    'driver' => $mysqlConfig['driver'],
    'host' => $mysqlConfig['host'],
    'dbname' => $mysqlConfig['database'],
    'user' => $mysqlConfig['username'],
    'password' => $mysqlConfig['password'],
    'charset' => $mysqlConfig['charset'],
];
$connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);

// Use with Eloquent ORM
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($pgsqlConfig, 'pgsql');
$capsule->setAsGlobal();
$capsule->bootEloquent();
```

### Paths to Different Cache Systems

A similar approach can be used for different cache systems:

```php
<?php

use Slim4\Root\Paths;

// Create Paths instance
$paths = new Paths(__DIR__, [
    // Standard paths
    'cache' => __DIR__ . '/var/cache',

    // Paths to different cache systems
    'cache.redis' => __DIR__ . '/var/cache/redis',
    'cache.memcached' => __DIR__ . '/var/cache/memcached',
    'cache.file' => __DIR__ . '/var/cache/file',
    'cache.apc' => __DIR__ . '/var/cache/apc',
]);

// Use paths to different cache systems
$redisPath = $paths->getPaths()['cache.redis'];
$memcachedPath = $paths->getPaths()['cache.memcached'];
$filePath = $paths->getPaths()['cache.file'];
$apcPath = $paths->getPaths()['cache.apc'];
```

## Framework Integration Use-Cases

### Laravel

Even though Laravel has its own system for path management, `slim4/root` can be useful in Laravel projects that have a non-traditional structure or need access to paths outside the Laravel application.

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Slim4\Root\Paths;
use Slim4\Root\PathsInterface;

class PathServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PathsInterface::class, function ($app) {
            return new Paths(
                base_path(),
                [
                    // Custom paths for Laravel
                    'config' => config_path(),
                    'resources' => resource_path(),
                    'views' => resource_path('views'),
                    'assets' => public_path('assets'),
                    'cache' => storage_path('framework/cache'),
                    'logs' => storage_path('logs'),
                    'public' => public_path(),
                    'database' => database_path(),
                    'migrations' => database_path('migrations'),
                    'storage' => storage_path(),
                    'tests' => base_path('tests'),
                ],
                false, // Disable auto-discovery, use Laravel paths
                false  // Disable path validation
            );
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
```

Then in `config/app.php` add the provider:

```php
'providers' => [
    // ...
    App\Providers\PathServiceProvider::class,
],
```

And use it in a controller:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Slim4\Root\PathsInterface;

class HomeController extends Controller
{
    protected $paths;

    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
    }

    public function index()
    {
        // Use paths
        $configPath = $this->paths->getConfigPath();
        $viewsPath = $this->paths->getViewsPath();

        // Access a file relative to the root directory
        $filePath = $this->paths->path('some/custom/directory/file.txt');

        return view('welcome', [
            'configPath' => $configPath,
            'viewsPath' => $viewsPath,
            'filePath' => $filePath,
        ]);
    }
}
```

### Slim 4 with Twig and Monolog

Complete example of integrating `slim4/root` with Slim 4, Twig, and Monolog:

```php
<?php

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Slim4\Root\Paths;
use Slim4\Root\PathsInterface;
use Slim4\Root\PathsMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

// Create container
$containerBuilder = new ContainerBuilder();

// Define services
$containerBuilder->addDefinitions([
    // Paths service
    PathsInterface::class => function () {
        $rootPath = dirname(__DIR__);
        return new Paths($rootPath, [], true, false);
    },

    // Twig service
    Twig::class => function (ContainerInterface $container) {
        $paths = $container->get(PathsInterface::class);
        $twig = Twig::create($paths->getViewsPath(), [
            'cache' => $paths->getCachePath() . '/twig',
            'auto_reload' => true,
        ]);

        // Add paths to Twig global variables
        $twig->getEnvironment()->addGlobal('paths', $paths);

        return $twig;
    },

    // Logger service
    Logger::class => function (ContainerInterface $container) {
        $paths = $container->get(PathsInterface::class);
        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler(
            $paths->getLogsPath() . '/app.log',
            Logger::DEBUG
        ));

        return $logger;
    },
]);

// Create container
$container = $containerBuilder->build();

// Create application
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add middleware
$app->add(TwigMiddleware::createFromContainer($app, Twig::class));
$app->add(new PathsMiddleware($container->get(PathsInterface::class)));

// Define routes
$app->get('/', function ($request, $response, $args) use ($container) {
    $paths = $request->getAttribute(PathsInterface::class);
    $logger = $container->get(Logger::class);

    // Log access
    $logger->info('Home page accessed');

    // Render template
    return $container->get(Twig::class)->render($response, 'home.twig', [
        'title' => 'Slim4 Root Example',
        'rootPath' => $paths->getRootPath(),
        'configPath' => $paths->getConfigPath(),
        'viewsPath' => $paths->getViewsPath(),
    ]);
});

// Run application
$app->run();
```

And the `views/home.twig` template:

```twig
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ title }}</title>
    <link rel="stylesheet" href="{{ paths.getPublicPath() }}/css/style.css">
</head>
<body>
    <h1>{{ title }}</h1>

    <h2>Paths:</h2>
    <ul>
        <li>Root Path: {{ rootPath }}</li>
        <li>Config Path: {{ configPath }}</li>
        <li>Views Path: {{ viewsPath }}</li>
    </ul>

    <script src="{{ paths.getPublicPath() }}/js/app.js"></script>
</body>
</html>
```

### Symfony

Even though Symfony has its own system for path management, `slim4/root` can be useful in Symfony projects that need access to paths outside the standard Symfony conventions.

```php
<?php

namespace App\Service;

use Slim4\Root\Paths;
use Slim4\Root\PathsInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class PathsService implements PathsInterface
{
    private PathsInterface $paths;

    public function __construct(KernelInterface $kernel, ParameterBagInterface $params)
    {
        $projectDir = $kernel->getProjectDir();

        $this->paths = new Paths(
            $projectDir,
            [
                // Custom paths for Symfony
                'config' => $projectDir . '/config',
                'resources' => $projectDir . '/resources',
                'views' => $projectDir . '/templates',
                'assets' => $projectDir . '/assets',
                'cache' => $kernel->getCacheDir(),
                'logs' => $kernel->getLogDir(),
                'public' => $projectDir . '/public',
                'database' => $projectDir . '/src/Database',
                'migrations' => $projectDir . '/migrations',
                'storage' => $projectDir . '/var',
                'tests' => $projectDir . '/tests',
            ],
            false, // Disable auto-discovery, use Symfony paths
            false  // Disable path validation
        );
    }

    // Implement all methods from PathsInterface
    public function getRootPath(): string
    {
        return $this->paths->getRootPath();
    }

    public function getConfigPath(): string
    {
        return $this->paths->getConfigPath();
    }

    // ... other methods ...

    public function path(string $path): string
    {
        return $this->paths->path($path);
    }

    public function getPaths(): array
    {
        return $this->paths->getPaths();
    }
}
```

Register the service in `config/services.yaml`:

```yaml
services:
    # ...
    App\Service\PathsService:
        arguments:
            - '@kernel'
            - '@parameter_bag'

    # Alias for the interface
    Slim4\Root\PathsInterface:
        alias: App\Service\PathsService
```

And use it in a controller:

```php
<?php

namespace App\Controller;

use Slim4\Root\PathsInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private PathsInterface $paths;

    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Use paths
        $configPath = $this->paths->getConfigPath();
        $viewsPath = $this->paths->getViewsPath();

        // Access a file relative to the root directory
        $filePath = $this->paths->path('some/custom/directory/file.txt');

        return $this->render('home/index.html.twig', [
            'configPath' => $configPath,
            'viewsPath' => $viewsPath,
            'filePath' => $filePath,
        ]);
    }
}
```

## Troubleshooting

### Path Doesn't Exist

If path validation is enabled and a path doesn't exist, an `InvalidPathException` will be thrown. The solution is either to create the missing directory or disable path validation:

```php
use Slim4\Root\Paths;

// Disable path validation
$paths = new Paths(__DIR__, [], true, false);
```

### Autoloading Issues

If you have issues with autoloading, make sure you have the correct autoloading setup in your `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "Slim4\\Root\\": "src/"
        }
    }
}
```

And then run:

```bash
composer dump-autoload
```

## Usage Examples

### Simple Example

For a simple example of using the `slim4/root` package in a typical Slim 4 project, see the [SIMPLE-EXAMPLE.en.md](SIMPLE-EXAMPLE.en.md) file.

### Integration with Template Engines

For examples of integrating the `slim4/root` package with popular PHP template engines (Blade, Plates, Volt, Twig, Latte, Smarty), see the [TEMPLATE-ENGINES.en.md](TEMPLATE-ENGINES.en.md) file.

### Advanced Use Cases

For detailed use cases of the `slim4/root` package in various scenarios and architectures, see the [USE-CASES.en.md](USE-CASES.en.md) file.

## License

The `slim4/root` package is licensed under the MIT license. This license is very permissive and allows you to use, modify, and distribute the code without major restrictions.

### Compatibility with Other Frameworks

Different PHP frameworks use different licenses:

- **Slim 4**: MIT license
- **Laravel**: MIT license
- **Symfony**: MIT license
- **Laminas** (formerly Zend Framework): BSD 3-Clause license

The `slim4/root` package is compatible with all these licenses, which means you can use it in projects based on any of these frameworks.

If you want to create a similar package for another framework, we recommend using a license that is compatible with the license of that framework.

## Contributing

Contributions are welcome and will be fully credited. We accept contributions via Pull Requests on [Github](https://github.com/responsive-sk/slim4-root).

### Pull Requests

- **[PSR-12 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-12-extended-coding-style-guide.md)** - The easiest way to apply the conventions is to install [PHP Code Sniffer](http://pear.php.net/package/PHP_CodeSniffer).
- **Add tests!** - Your patch won't be accepted if it doesn't have tests.
- **Document any change in behaviour** - Make sure the `README.md` and any other relevant documentation are kept up-to-date.
- **Consider our release cycle** - We try to follow [SemVer v2.0.0](http://semver.org/). Randomly breaking public APIs is not an option.
- **Create feature branches** - Don't ask us to pull from your master branch.
- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.
- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please [squash them](http://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages) before submitting.

### Running Tests

```bash
composer test
```

### Running PHP Code Sniffer

```bash
composer check-style
composer fix-style
```

### Running PHPStan

```bash
composer phpstan
```

**Happy coding!**
