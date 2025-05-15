# Slim4 Root

Root path management with auto-discovery and base path detection for Slim 4 applications. Say goodbye to relative paths like `__DIR__ . '/../../../config'` and hello to clean, consistent path access.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/responsive-sk/slim4-root.svg?style=flat-square)](https://packagist.org/packages/responsive-sk/slim4-root)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/responsive-sk/slim4-root.svg?style=flat-square)](https://packagist.org/packages/responsive-sk/slim4-root)

## New in 2.0

- **Automatic root path discovery** for common directory structures
- **Built-in path validation** with detailed error messages
- **Cross-platform path normalization** for Windows and Unix
- **Dedicated exception handling** for path-related errors
- **Enhanced developer experience** with more intuitive API
- **No more relative paths** with `../` - everything is relative to the root
- **Base path detection** for applications running in subdirectories

## Features

* Centralized path management for Slim 4 applications
* **Auto-discovery** of common directory structures
* Support for custom directory structures
* Path **validation** and **normalization**
* Middleware for accessing paths in route handlers
* **Base path detection** for applications running in subdirectories
* **Testing utilities** for easier test setup and execution
* PSR-11 container integration
* No dependencies (except Slim 4 and PSR Container)
* Fully tested
* Ready for PHP 7.4 and 8.0+

## Requirements

* PHP 7.4+ or 8.0+
* Slim 4

## Installation

```bash
composer require responsive-sk/slim4-root
```

## Usage

### Basic Usage

```php
use Slim4\Root\Paths;

// Create a new Paths instance with auto-discovery enabled
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

### With Custom Paths

```php
use Slim4\Root\Paths;

// Create a new Paths instance with custom paths
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

### Path Auto-Discovery

```php
use Slim4\Root\PathsDiscoverer;

// Create a new PathsDiscoverer instance
$discoverer = new PathsDiscoverer();

// Discover paths
$discoveredPaths = $discoverer->discover(__DIR__);

// Use discovered paths
var_dump($discoveredPaths);
```

### Path Validation

```php
use Slim4\Root\PathsValidator;
use Slim4\Root\Exception\InvalidPathException;

// Create a new PathsValidator instance
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

### Path Normalization

```php
use Slim4\Root\PathsNormalizer;

// Create a new PathsNormalizer instance
$normalizer = new PathsNormalizer();

// Normalize paths
$normalizedPath = $normalizer->normalize('C:\\path\\to\\project\\');
// Returns: 'C:/path/to/project'
```

### With DI Container

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

### With Paths Middleware

```php
use Slim4\Root\PathsMiddleware;
use Slim\Factory\AppFactory;

// Create app
$app = AppFactory::createFromContainer($container);

// Add the middleware to the app
$app->add($container->get(PathsMiddleware::class));

// Access paths in a route handler
$app->get('/', function ($request, $response) {
    $paths = $request->getAttribute('paths');
    $configPath = $paths->getConfigPath();

    // ...

    return $response;
});
```

### With Base Path Detection

```php
use Slim4\Root\BasePathMiddleware;
use Slim\Factory\AppFactory;

// Create app
$app = AppFactory::createFromContainer($container);

// Add the middleware to detect and set the base path
$app->add(new BasePathMiddleware($app));

// Now all routes will work correctly even if your app is in a subdirectory
$app->get('/', HomeAction::class);
```

### Integration with Twig

```php
use Slim\Views\Twig;
use Slim4\Root\PathsInterface;

// Register Twig with the container
$container->set(Twig::class, function (ContainerInterface $container) {
    $paths = $container->get(PathsInterface::class);

    $twig = Twig::create($paths->getViewsPath(), [
        'cache' => $paths->getCachePath() . '/twig',
        'debug' => true,
        'auto_reload' => true,
    ]);

    return $twig;
});
```

### Integration with Monolog

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Slim4\Root\PathsInterface;

// Register Logger with the container
$container->set(LoggerInterface::class, function (ContainerInterface $container) {
    $paths = $container->get(PathsInterface::class);

    $logger = new Logger('app');
    $logger->pushHandler(new StreamHandler($paths->getLogsPath() . '/app.log', Logger::DEBUG));

    return $logger;
});
```

### Testing Utilities

The package includes a `Testing` module that provides utilities for easier test setup and execution.

#### TestContainer

`TestContainer` is a simple static container for sharing objects between tests without using globals.

```php
use Slim4\Root\Testing\TestContainer;
use Slim4\Root\Paths;

// In your bootstrap file
$paths = new Paths(__DIR__);
TestContainer::set(Paths::class, $paths);

// In your tests
$paths = TestContainer::get(Paths::class);
```

#### Bootstrap File

The package includes a bootstrap file that you can use in your PHPUnit configuration:

```php
// tests/bootstrap.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/responsive-sk/slim4-root/src/Testing/bootstrap.php';
```

Or you can create your own bootstrap file:

```php
// tests/bootstrap.php
require_once __DIR__ . '/../vendor/autoload.php';

use Slim4\Root\Paths;
use Slim4\Root\Testing\TestContainer;

// Create Paths object
$rootPath = (string)realpath(__DIR__ . '/..');
$paths = new Paths($rootPath);

// Store in TestContainer
TestContainer::set(Paths::class, $paths);
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

### BasePathMiddleware

- `process(ServerRequestInterface $request, RequestHandlerInterface $handler)` - Process the request and set the base path for the Slim application

### PathsDiscoverer

- `discover(string $rootPath)` - Discover paths in the given root path

### PathsValidator

- `validate(array $paths, bool $strict)` - Validate paths

### PathsNormalizer

- `normalize(string $path)` - Normalize path

### TestContainer

- `set(string $key, mixed $value)` - Set an item in the container
- `get(string $key, mixed $default = null)` - Get an item from the container
- `has(string $key)` - Check if an item exists in the container
- `remove(string $key)` - Remove an item from the container
- `clear()` - Clear all items from the container

## Customizing Paths

You can customize the paths by passing an array of custom paths to the constructor:

```php
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
    ],
    true, // Enable auto-discovery
    false // Disable path validation
);
```

## Feature Comparison

| Feature | v1.x | v2.x |
|---------|------|------|
| Auto-discovery | ❌ No | ✅ Yes |
| Path validation | ❌ Basic | ✅ Comprehensive |
| Path normalization | ❌ No | ✅ Yes |
| Error handling | ❌ Generic exceptions | ✅ Dedicated exceptions |
| Relative paths | ❌ Manual `../` | ✅ Everything relative to root |
| Base path detection | ❌ No | ✅ Yes |
| Test coverage | ✅ Good | ✅ Excellent |
| Flexibility | ✅ Good | ✅ Excellent |

## Auto-Discovery

The `Paths` class can automatically discover common directory structures in your project. This is enabled by default, but you can disable it by passing `false` as the third parameter to the constructor.

```php
// With auto-discovery (default)
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

## Path Validation

The `Paths` class can validate that all paths exist. This is disabled by default, but you can enable it by passing `true` as the fourth parameter to the constructor.

```php
// Without validation (default)
$paths = new Paths(__DIR__);

// With validation
$paths = new Paths(__DIR__, [], true, true);
```

If validation is enabled and a path doesn't exist, an `InvalidPathException` will be thrown:

```php
try {
    $paths = new Paths(__DIR__, [], true, true);
} catch (\Slim4\Path\Exception\InvalidPathException $e) {
    echo $e->getMessage(); // "Configured path for 'views' is not a valid directory: /path/to/views"
}
```

## Testing

```bash
composer test
```

## Documentation

For detailed documentation, see:

- [Documentation (English)](docs/DOCUMENTATION.en.md)
- [Dokumentácia (Slovenčina)](docs/DOCUMENTATION.md)

### Examples

- [Simple Example](docs/SIMPLE-EXAMPLE.en.md) - Basic usage in a typical Slim 4 project
- [Template Engines Integration](docs/TEMPLATE-ENGINES.en.md) - Integration with popular PHP template engines
- [Advanced Use Cases](docs/USE-CASES.en.md) - Detailed use cases for various scenarios and architectures
- [Testing](docs/TESTING.en.md) - Testing utilities and integration with PHPUnit

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- [Responsive.sk Team](https://responsive.sk)
- [All Contributors](../../contributors)

## About Responsive.sk

Responsive.sk is a web development company specializing in creating modern, responsive web applications using the latest technologies and best practices.

## Roadmap

We're planning to expand this package with integrations for other frameworks and libraries. Check out our [TODO list](docs/TODO-COMMUNITY.md) for upcoming features and ways to contribute.

## Community

We're looking to collaborate with Laminas and Cycle ORM communities to create integrations for these frameworks. If you're interested in contributing, please check out our [TODO list](docs/TODO-COMMUNITY.md) and get in touch!
