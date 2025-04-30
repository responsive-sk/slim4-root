# Slim4 Root - Simple Usage Example

This document contains a simple example of using the `slim4/root` package in a typical Slim 4 project.

## Basic Project Structure

Let's imagine we have a standard Slim 4 project with the following structure:

```
my-project/
├── app/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Models/
│   └── Views/
├── config/
│   ├── container.php
│   ├── middleware.php
│   ├── routes.php
│   └── settings.php
├── public/
│   └── index.php
├── storage/
│   ├── cache/
│   └── logs/
├── tests/
├── vendor/
└── composer.json
```

## Installing the Package

First, let's install the `slim4/root` package using Composer:

```bash
composer require slim4/root
```

## Basic Usage

### 1. Creating a Paths Instance

In the `config/settings.php` file, we'll create a `Paths` instance:

```php
<?php

use Slim4\Root\Paths;

// Get the path to the project root directory
$rootPath = dirname(__DIR__);

// Create a Paths instance
$paths = new Paths($rootPath);

// Set up settings
return [
    'settings' => [
        'displayErrorDetails' => true,
        'logErrorDetails' => true,
        'logErrors' => true,
        'paths' => $paths,
    ],
];
```

### 2. Registering in the DI Container

In the `config/container.php` file, we'll register `Paths` in the DI container:

```php
<?php

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim4\Root\PathsInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Register Paths in the container
        PathsInterface::class => function (ContainerInterface $c) {
            return $c->get('settings')['paths'];
        },
    ]);
};
```

### 3. Adding Middleware

In the `config/middleware.php` file, we'll add the `PathsMiddleware`:

```php
<?php

use Slim\App;
use Slim4\Root\PathsMiddleware;
use Slim4\Root\PathsInterface;

return function (App $app) {
    // Add PathsMiddleware
    $app->add(function ($request, $handler) use ($app) {
        $paths = $app->getContainer()->get(PathsInterface::class);
        $middleware = new PathsMiddleware($paths);
        return $middleware->process($request, $handler);
    });
    
    // Other middleware...
};
```

### 4. Using in a Controller

Now we can use `Paths` in our controllers:

```php
<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim4\Root\PathsInterface;

class HomeController
{
    private PathsInterface $paths;
    
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
    }
    
    public function index(Request $request, Response $response): Response
    {
        // Get the path to the views directory
        $viewsPath = $this->paths->getViewsPath();
        
        // Load the template
        $template = file_get_contents($viewsPath . '/home.html');
        
        // Write to the response
        $response->getBody()->write($template);
        
        return $response;
    }
    
    public function uploadFile(Request $request, Response $response): Response
    {
        // Get the uploaded file
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['file'] ?? null;
        
        if ($uploadedFile) {
            // Get the path to the storage directory
            $storagePath = $this->paths->getStoragePath() . '/uploads';
            
            // Create the directory if it doesn't exist
            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0755, true);
            }
            
            // Save the file
            $filename = $uploadedFile->getClientFilename();
            $uploadedFile->moveTo($storagePath . '/' . $filename);
            
            $response->getBody()->write("File was successfully uploaded to: {$storagePath}/{$filename}");
        } else {
            $response->getBody()->write("No file was uploaded");
        }
        
        return $response;
    }
    
    public function log(Request $request, Response $response, array $args): Response
    {
        // Get the message from the URL
        $message = $args['message'] ?? 'No message';
        
        // Get the path to the logs directory
        $logsPath = $this->paths->getLogsPath();
        
        // Create the directory if it doesn't exist
        if (!is_dir($logsPath)) {
            mkdir($logsPath, 0755, true);
        }
        
        // Write to the log
        $logFile = $logsPath . '/app.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - {$message}\n", FILE_APPEND);
        
        $response->getBody()->write("Message was written to the log: {$logFile}");
        
        return $response;
    }
}
```

### 5. Registering Routes

In the `config/routes.php` file, we'll register our routes:

```php
<?php

use App\Controllers\HomeController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->get('/', [HomeController::class, 'index']);
    $app->post('/upload', [HomeController::class, 'uploadFile']);
    $app->get('/log/{message}', [HomeController::class, 'log']);
};
```

### 6. Running the Application

In the `public/index.php` file, we'll run the application:

```php
<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Create container
$containerBuilder = new ContainerBuilder();

// Load settings
$settings = require __DIR__ . '/../config/settings.php';
$containerBuilder->addDefinitions(['settings' => $settings]);

// Load container definitions
$dependencies = require __DIR__ . '/../config/container.php';
$dependencies($containerBuilder);

// Build container
$container = $containerBuilder->build();

// Create app
AppFactory::setContainer($container);
$app = AppFactory::create();

// Register middleware
$middleware = require __DIR__ . '/../config/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../config/routes.php';
$routes($app);

// Run app
$app->run();
```

## Benefits of Using the `slim4/root` Package

1. **No relative paths** - You don't need to use `__DIR__ . '/../../../'` in your code
2. **Consistent access** - All paths are available through a unified interface
3. **Easier testing** - Easier mocking of paths in tests
4. **Flexibility** - Easy to change project structure without changing code

## More Usage Examples

### Accessing Paths from Middleware

```php
<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim4\Root\PathsInterface;

class LoggingMiddleware implements MiddlewareInterface
{
    private PathsInterface $paths;
    
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get the path to the logs directory
        $logsPath = $this->paths->getLogsPath();
        
        // Create the directory if it doesn't exist
        if (!is_dir($logsPath)) {
            mkdir($logsPath, 0755, true);
        }
        
        // Write to the log
        $logFile = $logsPath . '/requests.log';
        $logMessage = date('Y-m-d H:i:s') . " - {$request->getMethod()} {$request->getUri()->getPath()}\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Continue processing the request
        return $handler->handle($request);
    }
}
```

### Accessing Paths from a Model

```php
<?php

namespace App\Models;

use Slim4\Root\PathsInterface;

class User
{
    private PathsInterface $paths;
    
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
    }
    
    public function saveAvatar(int $userId, string $avatarData): string
    {
        // Get the path to the storage directory
        $storagePath = $this->paths->getStoragePath() . '/avatars';
        
        // Create the directory if it doesn't exist
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
        
        // Save the avatar
        $avatarPath = $storagePath . '/' . $userId . '.jpg';
        file_put_contents($avatarPath, base64_decode($avatarData));
        
        return $avatarPath;
    }
    
    public function getAvatarPath(int $userId): string
    {
        return $this->paths->getStoragePath() . '/avatars/' . $userId . '.jpg';
    }
}
```

### Accessing Paths from a View

```php
<?php

namespace App\Views;

use Slim4\Root\PathsInterface;

class TemplateRenderer
{
    private PathsInterface $paths;
    
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
    }
    
    public function render(string $template, array $data = []): string
    {
        // Get the path to the views directory
        $viewsPath = $this->paths->getViewsPath();
        
        // Load the template
        $templatePath = $viewsPath . '/' . $template . '.php';
        
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template not found: {$templatePath}");
        }
        
        // Extract data for the template
        extract($data);
        
        // Capture output
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
}
```

## Conclusion

This simple example shows how you can use the `slim4/root` package in a typical Slim 4 project. The package helps you get rid of relative paths and provides a unified way to access directories and files in your project.

For more advanced usage, such as integration with hexagonal architecture or Domain-Driven Design, see the [USE-CASES.en.md](USE-CASES.en.md) file.
