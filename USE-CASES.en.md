# Slim4 Root - Use Cases

This document contains detailed use cases for the `slim4/root` package in various scenarios and architectures.

## Table of Contents

1. [Hexagonal Architecture (Ports & Adapters)](#hexagonal-architecture-ports--adapters)
2. [Domain-Driven Design (DDD)](#domain-driven-design-ddd)
3. [Working with Multiple Databases](#working-with-multiple-databases)
4. [Framework Integration](#framework-integration)
5. [Testing](#testing)
6. [Deployment](#deployment)

## Hexagonal Architecture (Ports & Adapters)

Hexagonal Architecture (also known as Ports & Adapters) is an architectural pattern that separates the core of an application (the domain) from the outside world (infrastructure). The `slim4/root` package can significantly simplify the implementation of this architecture.

### Project Structure

A typical project structure with hexagonal architecture might look like this:

```
project/
├── src/
│   ├── Domain/           # Domain models and business logic
│   ├── Application/      # Application services and ports (interfaces)
│   │   └── Ports/        # Interfaces for communication with the outside world
│   ├── Infrastructure/   # Adapter implementations
│   │   └── Adapters/     # Concrete implementations of ports
│   └── Interfaces/       # User interfaces (API, CLI, Web)
├── config/               # Configuration files
├── public/               # Public files
└── tests/                # Tests
```

### Path Configuration

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
```

### Example Usage in Application

```php
<?php

namespace App\Application\Services;

use App\Application\Ports\UserRepositoryInterface;
use App\Domain\User\User;
use Slim4\Root\PathsInterface;

class UserService
{
    private UserRepositoryInterface $userRepository;
    private PathsInterface $paths;
    
    public function __construct(UserRepositoryInterface $userRepository, PathsInterface $paths)
    {
        $this->userRepository = $userRepository;
        $this->paths = $paths;
    }
    
    public function createUser(string $name, string $email): User
    {
        // Create a new user
        $user = new User($name, $email);
        
        // Save the user
        $this->userRepository->save($user);
        
        // Log the action
        $logPath = $this->paths->getLogsPath() . '/users.log';
        file_put_contents($logPath, date('Y-m-d H:i:s') . " - Created user: {$name} ({$email})\n", FILE_APPEND);
        
        return $user;
    }
    
    public function getUserProfilePicturePath(int $userId): string
    {
        // Get the path to profile pictures
        return $this->paths->getStoragePath() . '/users/' . $userId . '/profile.jpg';
    }
}
```

### Benefits of Using `slim4/root` in Hexagonal Architecture

1. **Consistent path access** - All paths are defined in one place
2. **Separation of domain from infrastructure** - Domain doesn't need to know about specific paths
3. **Easier testing** - Easier mocking of paths in tests
4. **Flexibility** - Easy to change project structure without changing code

## Domain-Driven Design (DDD)

Domain-Driven Design (DDD) is an approach to software development that focuses on the domain and domain models. The `slim4/root` package can help organize a project according to DDD principles.

### Project Structure with Bounded Contexts

```
project/
├── src/
│   ├── Contexts/
│   │   ├── User/              # Bounded Context for users
│   │   │   ├── Domain/        # Domain models and business logic
│   │   │   ├── Application/   # Application services
│   │   │   └── Infrastructure/# Infrastructure implementations
│   │   ├── Product/           # Bounded Context for products
│   │   └── Order/             # Bounded Context for orders
│   └── Shared/                # Shared components
├── config/                    # Configuration files
├── public/                    # Public files
└── tests/                     # Tests
```

### Path Configuration

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
    
    // Shared components
    'shared' => __DIR__ . '/src/Shared',
]);
```

### Example Usage in Application

```php
<?php

namespace App\Contexts\User\Application\Services;

use App\Contexts\User\Domain\Entities\User;
use App\Contexts\User\Domain\Repositories\UserRepositoryInterface;
use App\Contexts\User\Domain\ValueObjects\Email;
use App\Contexts\User\Domain\ValueObjects\UserId;
use Slim4\Root\PathsInterface;

class UserService
{
    private UserRepositoryInterface $userRepository;
    private PathsInterface $paths;
    
    public function __construct(UserRepositoryInterface $userRepository, PathsInterface $paths)
    {
        $this->userRepository = $userRepository;
        $this->paths = $paths;
    }
    
    public function registerUser(string $name, string $email): User
    {
        // Create value objects
        $userId = UserId::generate();
        $emailVO = new Email($email);
        
        // Create entity
        $user = User::create($userId, $name, $emailVO);
        
        // Save the user
        $this->userRepository->save($user);
        
        // Create directory for user
        $userStoragePath = $this->paths->getStoragePath() . '/users/' . $userId->toString();
        if (!is_dir($userStoragePath)) {
            mkdir($userStoragePath, 0755, true);
        }
        
        return $user;
    }
    
    public function getUserDocumentsPath(UserId $userId): string
    {
        return $this->paths->getStoragePath() . '/users/' . $userId->toString() . '/documents';
    }
}
```

### Benefits of Using `slim4/root` in DDD

1. **Organization of Bounded Contexts** - Clear separation of different contexts
2. **Management of domain models** - Easier access to entities, value objects, and aggregates
3. **Support for layered architecture** - Clear separation of domain, application, and infrastructure
4. **Flexibility** - Ability to add new contexts without changing existing code

## Working with Multiple Databases

The `slim4/root` package can significantly simplify working with multiple databases in a single project.

### Path Configuration for Different Databases

```php
<?php

use Slim4\Root\Paths;

// Create Paths instance for different databases
$paths = new Paths(__DIR__, [
    // Base paths
    'database' => __DIR__ . '/database',
    
    // Paths to different databases
    'database.sqlite' => __DIR__ . '/database/sqlite',
    'database.mysql' => __DIR__ . '/database/mysql',
    'database.pgsql' => __DIR__ . '/database/pgsql',
    
    // Paths to migrations for different databases
    'migrations.sqlite' => __DIR__ . '/database/sqlite/migrations',
    'migrations.mysql' => __DIR__ . '/database/mysql/migrations',
    'migrations.pgsql' => __DIR__ . '/database/pgsql/migrations',
    
    // Paths to configuration files
    'config.database.sqlite' => __DIR__ . '/config/database/sqlite.php',
    'config.database.mysql' => __DIR__ . '/config/database/mysql.php',
    'config.database.pgsql' => __DIR__ . '/config/database/pgsql.php',
]);
```

### Example Usage in Application

```php
<?php

namespace App\Database;

use PDO;
use Slim4\Root\PathsInterface;

class DatabaseFactory
{
    private PathsInterface $paths;
    
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
    }
    
    public function createConnection(string $driver = 'mysql'): PDO
    {
        // Load configuration
        $configPath = $this->paths->getPaths()['config.database.' . $driver];
        $config = require $configPath;
        
        // Create connection based on driver
        switch ($driver) {
            case 'sqlite':
                $dbPath = $this->paths->getPaths()['database.sqlite'] . '/database.sqlite';
                return new PDO('sqlite:' . $dbPath);
                
            case 'mysql':
                $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
                return new PDO($dsn, $config['username'], $config['password']);
                
            case 'pgsql':
                $dsn = "pgsql:host={$config['host']};dbname={$config['database']}";
                return new PDO($dsn, $config['username'], $config['password']);
                
            default:
                throw new \InvalidArgumentException("Unsupported driver: {$driver}");
        }
    }
    
    public function getMigrationsPath(string $driver): string
    {
        return $this->paths->getPaths()['migrations.' . $driver];
    }
}
```

### Example Usage with Doctrine DBAL

```php
<?php

namespace App\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Slim4\Root\PathsInterface;

class DoctrineConnectionFactory
{
    private PathsInterface $paths;
    
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
    }
    
    public function createConnection(string $driver = 'mysql'): Connection
    {
        // Load configuration
        $configPath = $this->paths->getPaths()['config.database.' . $driver];
        $config = require $configPath;
        
        // Create connection parameters
        $connectionParams = [
            'driver' => $driver,
            'host' => $config['host'] ?? null,
            'dbname' => $config['database'] ?? null,
            'user' => $config['username'] ?? null,
            'password' => $config['password'] ?? null,
            'charset' => $config['charset'] ?? null,
        ];
        
        // For SQLite, set the path to the file
        if ($driver === 'sqlite') {
            $dbPath = $this->paths->getPaths()['database.sqlite'] . '/database.sqlite';
            $connectionParams = [
                'driver' => 'pdo_sqlite',
                'path' => $dbPath,
            ];
        }
        
        // Create connection
        return DriverManager::getConnection($connectionParams);
    }
}
```

### Benefits of Using `slim4/root` for Working with Multiple Databases

1. **Centralized path management** - All database paths are defined in one place
2. **Flexibility** - Easy switching between different databases
3. **Clean code** - No hardcoded paths in code
4. **Easier testing** - Easier mocking of paths in tests

## Framework Integration

The `slim4/root` package can be integrated with various PHP frameworks.

### Slim 4

```php
<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim4\Root\Paths;
use Slim4\Root\PathsInterface;
use Slim4\Root\PathsMiddleware;

// Create Paths instance
$rootPath = dirname(__DIR__);
$paths = new Paths($rootPath);

// Create container
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    PathsInterface::class => $paths,
]);
$container = $containerBuilder->build();

// Create application
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add middleware
$app->add(new PathsMiddleware($paths));

// Define route
$app->get('/', function ($request, $response) {
    $paths = $request->getAttribute(PathsInterface::class);
    $response->getBody()->write('Root path: ' . $paths->getRootPath());
    return $response;
});

// Run application
$app->run();
```

### Laravel

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
                ]
            );
        });
    }
}
```

### Symfony

```php
<?php

namespace App\Service;

use Slim4\Root\Paths;
use Slim4\Root\PathsInterface;
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
            ]
        );
    }

    // Implement all methods from PathsInterface
    public function getRootPath(): string
    {
        return $this->paths->getRootPath();
    }
    
    // ... other methods ...
}
```

## Testing

The `slim4/root` package can significantly simplify testing your application.

### Path Configuration for Tests

```php
<?php

use Slim4\Root\Paths;

// Create Paths instance for tests
$paths = new Paths(__DIR__, [
    // Base paths
    'tests' => __DIR__ . '/tests',
    
    // Paths for different types of tests
    'tests.unit' => __DIR__ . '/tests/Unit',
    'tests.integration' => __DIR__ . '/tests/Integration',
    'tests.functional' => __DIR__ . '/tests/Functional',
    
    // Paths for fixtures
    'tests.fixtures' => __DIR__ . '/tests/Fixtures',
    
    // Paths for temporary files
    'tests.temp' => __DIR__ . '/tests/temp',
]);
```

### Example Usage in Tests

```php
<?php

namespace Tests\Unit;

use App\User\UserService;
use App\User\UserRepository;
use PHPUnit\Framework\TestCase;
use Slim4\Root\Paths;

class UserServiceTest extends TestCase
{
    private Paths $paths;
    
    protected function setUp(): void
    {
        // Create Paths instance for tests
        $this->paths = new Paths(__DIR__ . '/../..', [
            'tests.fixtures' => __DIR__ . '/../Fixtures',
            'tests.temp' => __DIR__ . '/../temp',
        ]);
        
        // Create temporary directory
        if (!is_dir($this->paths->getPaths()['tests.temp'])) {
            mkdir($this->paths->getPaths()['tests.temp'], 0755, true);
        }
    }
    
    public function testCreateUser(): void
    {
        // Create mock repository
        $userRepository = $this->createMock(UserRepository::class);
        
        // Create service
        $userService = new UserService($userRepository, $this->paths);
        
        // Test
        $user = $userService->createUser('John Doe', 'john@example.com');
        
        // Assertions
        $this->assertSame('John Doe', $user->getName());
        $this->assertSame('john@example.com', $user->getEmail());
        
        // Check if log file was created
        $logPath = $this->paths->getPaths()['tests.temp'] . '/users.log';
        $this->assertFileExists($logPath);
        $this->assertStringContainsString('Created user: John Doe (john@example.com)', file_get_contents($logPath));
    }
    
    protected function tearDown(): void
    {
        // Clean up temporary files
        $tempDir = $this->paths->getPaths()['tests.temp'];
        if (is_dir($tempDir)) {
            $files = glob($tempDir . '/*');
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }
}
```

### Benefits of Using `slim4/root` for Testing

1. **Consistent paths** - Same paths in production code and tests
2. **Test isolation** - Easy separation of test files from production files
3. **Easier mocking** - Easier creation of mock objects for tests
4. **Cleaner tests** - No hardcoded paths in tests

## Deployment

The `slim4/root` package can also help with deploying your application to different environments.

### Path Configuration for Different Environments

```php
<?php

use Slim4\Root\Paths;

// Detect environment
$env = getenv('APP_ENV') ?: 'production';

// Base path
$rootPath = dirname(__DIR__);

// Create Paths instance based on environment
switch ($env) {
    case 'development':
        $paths = new Paths($rootPath, [
            'cache' => $rootPath . '/var/cache/dev',
            'logs' => $rootPath . '/var/logs/dev',
        ]);
        break;
        
    case 'testing':
        $paths = new Paths($rootPath, [
            'cache' => $rootPath . '/var/cache/test',
            'logs' => $rootPath . '/var/logs/test',
        ]);
        break;
        
    case 'staging':
        $paths = new Paths($rootPath, [
            'cache' => $rootPath . '/var/cache/staging',
            'logs' => $rootPath . '/var/logs/staging',
        ]);
        break;
        
    case 'production':
    default:
        $paths = new Paths($rootPath, [
            'cache' => $rootPath . '/var/cache/prod',
            'logs' => $rootPath . '/var/logs/prod',
        ]);
        break;
}
```

### Example Usage in Deployment Script

```php
<?php

namespace App\Deployment;

use Slim4\Root\PathsInterface;

class DeploymentManager
{
    private PathsInterface $paths;
    
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
    }
    
    public function deploy(): void
    {
        // Clear cache
        $this->clearCache();
        
        // Run migrations
        $this->runMigrations();
        
        // Create necessary directories
        $this->createDirectories();
        
        // Set permissions
        $this->setPermissions();
    }
    
    private function clearCache(): void
    {
        $cacheDir = $this->paths->getCachePath();
        $this->removeDirectory($cacheDir);
        mkdir($cacheDir, 0755, true);
    }
    
    private function runMigrations(): void
    {
        $migrationsDir = $this->paths->getMigrationsPath();
        // Run migrations...
    }
    
    private function createDirectories(): void
    {
        $directories = [
            $this->paths->getLogsPath(),
            $this->paths->getStoragePath() . '/uploads',
            $this->paths->getStoragePath() . '/cache',
            $this->paths->getStoragePath() . '/sessions',
        ];
        
        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
        }
    }
    
    private function setPermissions(): void
    {
        $writableDirs = [
            $this->paths->getCachePath(),
            $this->paths->getLogsPath(),
            $this->paths->getStoragePath(),
        ];
        
        foreach ($writableDirs as $dir) {
            chmod($dir, 0777);
        }
    }
    
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }
}
```

### Benefits of Using `slim4/root` for Deployment

1. **Consistent paths** - Same paths across all environments
2. **Flexibility** - Easy adaptation of paths for different environments
3. **Automation** - Easier automation of deployment processes
4. **Security** - Better control over file and directory permissions
