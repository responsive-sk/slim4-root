# Testing with Slim4 Root

The `slim4-root` package provides tools to simplify testing applications built on the Slim 4 framework.

## Contents

- [TestContainer](#testcontainer)
- [Bootstrap file](#bootstrap-file)
- [Integration with PHPUnit](#integration-with-phpunit)
- [Usage examples](#usage-examples)

## TestContainer

`TestContainer` is a simple static container for sharing objects between tests without using global variables.

### Basic usage

```php
use Slim4\Root\Testing\TestContainer;
use Slim4\Root\Paths;

// In your bootstrap file
$paths = new Paths(__DIR__);
TestContainer::set(Paths::class, $paths);

// In your tests
$paths = TestContainer::get(Paths::class);
```

### Available methods

- `set(string $key, mixed $value)` - Set a value in the container
- `get(string $key, mixed $default = null)` - Get a value from the container
- `has(string $key)` - Check if a value exists in the container
- `remove(string $key)` - Remove a value from the container
- `clear()` - Clear all values from the container

## Bootstrap file

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

// Create container
$containerBuilder = new \DI\ContainerBuilder();

// Add Paths to container
$containerBuilder->addDefinitions([
    Paths::class => $paths,
]);

// Load dependencies
if (file_exists(__DIR__ . '/../config/dependencies.php')) {
    /** @var array<string, mixed> $dependencies */
    $dependencies = require __DIR__ . '/../config/dependencies.php';
    $containerBuilder->addDefinitions($dependencies);
}

// Build container
$container = $containerBuilder->build();

// Store container in TestContainer
TestContainer::set('container', $container);
```

## Integration with PHPUnit

Edit your `phpunit.xml` file to use the bootstrap file:

```xml
<phpunit bootstrap="tests/bootstrap.php">
    <!-- ... -->
</phpunit>
```

## Usage examples

### Accessing paths in tests

```php
use PHPUnit\Framework\TestCase;
use Slim4\Root\Paths;
use Slim4\Root\Testing\TestContainer;

class MyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Get Paths object from TestContainer
        $this->paths = TestContainer::get(Paths::class);
    }
    
    public function testSomething(): void
    {
        // Use Paths object
        $configPath = $this->paths->getConfigPath();
        
        // ...
    }
}
```

### Accessing container in tests

```php
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Slim4\Root\Testing\TestContainer;

class MyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Get container from TestContainer
        $this->container = TestContainer::get('container');
    }
    
    public function testSomething(): void
    {
        // Use container
        $service = $this->container->get('my-service');
        
        // ...
    }
}
```

### Creating application in tests

```php
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim4\Root\Testing\TestContainer;

class MyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Get container from TestContainer
        $this->container = TestContainer::get('container');
        
        // Create application
        $this->app = AppFactory::createFromContainer($this->container);
    }
    
    public function testSomething(): void
    {
        // Use application
        $this->app->get('/', function ($request, $response) {
            return $response->withJson(['status' => 'ok']);
        });
        
        // ...
    }
}
```
