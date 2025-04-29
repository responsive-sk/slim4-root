# Slim4 Root - Jednoduchý príklad použitia

Tento dokument obsahuje jednoduchý príklad použitia balíka `slim4/root` v bežnom Slim 4 projekte.

## Základná štruktúra projektu

Predstavme si, že máme štandardný Slim 4 projekt s nasledujúcou štruktúrou:

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

## Inštalácia balíka

Najprv nainštalujeme balík `slim4/root` pomocou Composera:

```bash
composer require slim4/root
```

## Základné použitie

### 1. Vytvorenie inštancie Paths

V súbore `config/settings.php` vytvoríme inštanciu `Paths`:

```php
<?php

use Slim4\Root\Paths;

// Získanie cesty k root adresáru projektu
$rootPath = dirname(__DIR__);

// Vytvorenie inštancie Paths
$paths = new Paths($rootPath);

// Nastavenie ciest
return [
    'settings' => [
        'displayErrorDetails' => true,
        'logErrorDetails' => true,
        'logErrors' => true,
        'paths' => $paths,
    ],
];
```

### 2. Registrácia v DI kontajneri

V súbore `config/container.php` zaregistrujeme `Paths` do DI kontajnera:

```php
<?php

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim4\Root\PathsInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Registrácia Paths do kontajnera
        PathsInterface::class => function (ContainerInterface $c) {
            return $c->get('settings')['paths'];
        },
    ]);
};
```

### 3. Pridanie middleware

V súbore `config/middleware.php` pridáme `PathsMiddleware`:

```php
<?php

use Slim\App;
use Slim4\Root\PathsMiddleware;
use Slim4\Root\PathsInterface;

return function (App $app) {
    // Pridanie PathsMiddleware
    $app->add(function ($request, $handler) use ($app) {
        $paths = $app->getContainer()->get(PathsInterface::class);
        $middleware = new PathsMiddleware($paths);
        return $middleware->process($request, $handler);
    });
    
    // Ostatné middleware...
};
```

### 4. Použitie v kontroleri

Teraz môžeme používať `Paths` v našich kontroleroch:

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
        // Získanie cesty k views adresáru
        $viewsPath = $this->paths->getViewsPath();
        
        // Načítanie šablóny
        $template = file_get_contents($viewsPath . '/home.html');
        
        // Zápis do response
        $response->getBody()->write($template);
        
        return $response;
    }
    
    public function uploadFile(Request $request, Response $response): Response
    {
        // Získanie uploadovaného súboru
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['file'] ?? null;
        
        if ($uploadedFile) {
            // Získanie cesty k storage adresáru
            $storagePath = $this->paths->getStoragePath() . '/uploads';
            
            // Vytvorenie adresára, ak neexistuje
            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0755, true);
            }
            
            // Uloženie súboru
            $filename = $uploadedFile->getClientFilename();
            $uploadedFile->moveTo($storagePath . '/' . $filename);
            
            $response->getBody()->write("Súbor bol úspešne nahraný do: {$storagePath}/{$filename}");
        } else {
            $response->getBody()->write("Žiadny súbor nebol nahraný");
        }
        
        return $response;
    }
    
    public function log(Request $request, Response $response, array $args): Response
    {
        // Získanie správy z URL
        $message = $args['message'] ?? 'No message';
        
        // Získanie cesty k logs adresáru
        $logsPath = $this->paths->getLogsPath();
        
        // Vytvorenie adresára, ak neexistuje
        if (!is_dir($logsPath)) {
            mkdir($logsPath, 0755, true);
        }
        
        // Zápis do logu
        $logFile = $logsPath . '/app.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - {$message}\n", FILE_APPEND);
        
        $response->getBody()->write("Správa bola zapísaná do logu: {$logFile}");
        
        return $response;
    }
}
```

### 5. Registrácia rút

V súbore `config/routes.php` zaregistrujeme naše rúty:

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

### 6. Spustenie aplikácie

V súbore `public/index.php` spustíme aplikáciu:

```php
<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Vytvorenie kontajnera
$containerBuilder = new ContainerBuilder();

// Načítanie nastavení
$settings = require __DIR__ . '/../config/settings.php';
$containerBuilder->addDefinitions(['settings' => $settings]);

// Načítanie definícií kontajnera
$dependencies = require __DIR__ . '/../config/container.php';
$dependencies($containerBuilder);

// Vytvorenie kontajnera
$container = $containerBuilder->build();

// Vytvorenie aplikácie
AppFactory::setContainer($container);
$app = AppFactory::create();

// Registrácia middleware
$middleware = require __DIR__ . '/../config/middleware.php';
$middleware($app);

// Registrácia rút
$routes = require __DIR__ . '/../config/routes.php';
$routes($app);

// Spustenie aplikácie
$app->run();
```

## Výhody použitia balíka `slim4/root`

1. **Žiadne relatívne cesty** - Nemusíte používať `__DIR__ . '/../../../'` v kóde
2. **Konzistentný prístup** - Všetky cesty sú dostupné cez jednotné rozhranie
3. **Jednoduchšie testovanie** - Ľahšie mocknutie ciest v testoch
4. **Flexibilita** - Ľahká zmena štruktúry projektu bez nutnosti meniť kód

## Ďalšie príklady použitia

### Prístup k cestám z middleware

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
        // Získanie cesty k logs adresáru
        $logsPath = $this->paths->getLogsPath();
        
        // Vytvorenie adresára, ak neexistuje
        if (!is_dir($logsPath)) {
            mkdir($logsPath, 0755, true);
        }
        
        // Zápis do logu
        $logFile = $logsPath . '/requests.log';
        $logMessage = date('Y-m-d H:i:s') . " - {$request->getMethod()} {$request->getUri()->getPath()}\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Pokračovanie v spracovaní požiadavky
        return $handler->handle($request);
    }
}
```

### Prístup k cestám z modelu

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
        // Získanie cesty k storage adresáru
        $storagePath = $this->paths->getStoragePath() . '/avatars';
        
        // Vytvorenie adresára, ak neexistuje
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
        
        // Uloženie avatara
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

### Prístup k cestám z view

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
        // Získanie cesty k views adresáru
        $viewsPath = $this->paths->getViewsPath();
        
        // Načítanie šablóny
        $templatePath = $viewsPath . '/' . $template . '.php';
        
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template not found: {$templatePath}");
        }
        
        // Extrakcia dát pre šablónu
        extract($data);
        
        // Zachytenie výstupu
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
}
```

## Záver

Tento jednoduchý príklad ukazuje, ako môžete používať balík `slim4/root` v bežnom Slim 4 projekte. Balík vám pomôže zbaviť sa relatívnych ciest a poskytne vám jednotný spôsob prístupu k adresárom a súborom vo vašom projekte.

Pre pokročilejšie použitie, ako je integrácia s hexagonálnou architektúrou alebo Domain-Driven Design, pozrite si súbor [USE-CASES.md](USE-CASES.md).
