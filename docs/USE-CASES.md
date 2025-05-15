# Slim4 Root - Prípady použitia

Tento dokument obsahuje podrobné prípady použitia balíka `slim4/root` v rôznych scenároch a architektúrach.

## Obsah

1. [Hexagonálna architektúra (Ports & Adapters)](#hexagonálna-architektúra-ports--adapters)
2. [Domain-Driven Design (DDD)](#domain-driven-design-ddd)
3. [Práca s viacerými databázami](#práca-s-viacerými-databázami)
4. [Integrácia s frameworkami](#integrácia-s-frameworkami)
5. [Testovanie](#testovanie)
6. [Nasadenie (Deployment)](#nasadenie-deployment)

## Hexagonálna architektúra (Ports & Adapters)

Hexagonálna architektúra (známa aj ako Ports & Adapters) je architektonický vzor, ktorý oddeľuje jadro aplikácie (doménu) od vonkajšieho sveta (infraštruktúry). Balík `slim4/root` môže výrazne zjednodušiť implementáciu tejto architektúry.

### Štruktúra projektu

Typická štruktúra projektu s hexagonálnou architektúrou môže vyzerať takto:

```
project/
├── src/
│   ├── Domain/           # Doménové modely a biznis logika
│   ├── Application/      # Aplikačné služby a porty (rozhrania)
│   │   └── Ports/        # Rozhrania pre komunikáciu s vonkajším svetom
│   ├── Infrastructure/   # Implementácie adapterov
│   │   └── Adapters/     # Konkrétne implementácie portov
│   └── Interfaces/       # Užívateľské rozhrania (API, CLI, Web)
├── config/               # Konfiguračné súbory
├── public/               # Verejné súbory
└── tests/                # Testy
```

### Konfigurácia ciest

```php
<?php

use Slim4\Root\Paths;

// Vytvorenie inštancie Paths pre hexagonálnu architektúru
$paths = new Paths(__DIR__, [
    // Základné cesty
    'domain' => __DIR__ . '/src/Domain',
    'application' => __DIR__ . '/src/Application',
    'infrastructure' => __DIR__ . '/src/Infrastructure',
    'interfaces' => __DIR__ . '/src/Interfaces',
    
    // Cesty pre porty a adaptery
    'ports' => __DIR__ . '/src/Application/Ports',
    'adapters' => __DIR__ . '/src/Infrastructure/Adapters',
    
    // Cesty pre testy
    'tests.domain' => __DIR__ . '/tests/Domain',
    'tests.application' => __DIR__ . '/tests/Application',
    'tests.infrastructure' => __DIR__ . '/tests/Infrastructure',
]);
```

### Príklad použitia v aplikácii

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
        // Vytvorenie nového používateľa
        $user = new User($name, $email);
        
        // Uloženie používateľa
        $this->userRepository->save($user);
        
        // Logovanie akcie
        $logPath = $this->paths->getLogsPath() . '/users.log';
        file_put_contents($logPath, date('Y-m-d H:i:s') . " - Created user: {$name} ({$email})\n", FILE_APPEND);
        
        return $user;
    }
    
    public function getUserProfilePicturePath(int $userId): string
    {
        // Získanie cesty k profilovým obrázkom
        return $this->paths->getStoragePath() . '/users/' . $userId . '/profile.jpg';
    }
}
```

### Výhody použitia `slim4/root` v hexagonálnej architektúre

1. **Konzistentný prístup k cestám** - Všetky cesty sú definované na jednom mieste
2. **Oddelenie domény od infraštruktúry** - Doména nemusí vedieť o konkrétnych cestách
3. **Jednoduchšie testovanie** - Ľahšie mocknutie ciest v testoch
4. **Flexibilita** - Ľahká zmena štruktúry projektu bez nutnosti meniť kód

## Domain-Driven Design (DDD)

Domain-Driven Design (DDD) je prístup k vývoju softvéru, ktorý sa zameriava na doménu a doménové modely. Balík `slim4/root` môže pomôcť s organizáciou projektu podľa princípov DDD.

### Štruktúra projektu s Bounded Contexts

```
project/
├── src/
│   ├── Contexts/
│   │   ├── User/              # Bounded Context pre používateľov
│   │   │   ├── Domain/        # Doménové modely a biznis logika
│   │   │   ├── Application/   # Aplikačné služby
│   │   │   └── Infrastructure/# Implementácie infraštruktúry
│   │   ├── Product/           # Bounded Context pre produkty
│   │   └── Order/             # Bounded Context pre objednávky
│   └── Shared/                # Zdieľané komponenty
├── config/                    # Konfiguračné súbory
├── public/                    # Verejné súbory
└── tests/                     # Testy
```

### Konfigurácia ciest

```php
<?php

use Slim4\Root\Paths;

// Vytvorenie inštancie Paths pre DDD
$paths = new Paths(__DIR__, [
    // Bounded Contexts
    'contexts.user' => __DIR__ . '/src/Contexts/User',
    'contexts.product' => __DIR__ . '/src/Contexts/Product',
    'contexts.order' => __DIR__ . '/src/Contexts/Order',
    
    // Vrstvy v rámci kontextu
    'contexts.user.domain' => __DIR__ . '/src/Contexts/User/Domain',
    'contexts.user.application' => __DIR__ . '/src/Contexts/User/Application',
    'contexts.user.infrastructure' => __DIR__ . '/src/Contexts/User/Infrastructure',
    
    // Doménové komponenty
    'contexts.user.domain.entities' => __DIR__ . '/src/Contexts/User/Domain/Entities',
    'contexts.user.domain.value-objects' => __DIR__ . '/src/Contexts/User/Domain/ValueObjects',
    'contexts.user.domain.repositories' => __DIR__ . '/src/Contexts/User/Domain/Repositories',
    
    // Zdieľané komponenty
    'shared' => __DIR__ . '/src/Shared',
]);
```

### Príklad použitia v aplikácii

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
        // Vytvorenie value objektov
        $userId = UserId::generate();
        $emailVO = new Email($email);
        
        // Vytvorenie entity
        $user = User::create($userId, $name, $emailVO);
        
        // Uloženie používateľa
        $this->userRepository->save($user);
        
        // Vytvorenie adresára pre používateľa
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

### Výhody použitia `slim4/root` v DDD

1. **Organizácia Bounded Contexts** - Jasné oddelenie rôznych kontextov
2. **Správa doménových modelov** - Ľahší prístup k entitám, value objektom a agregátom
3. **Podpora vrstvovej architektúry** - Jasné oddelenie domény, aplikácie a infraštruktúry
4. **Flexibilita** - Možnosť pridávať nové kontexty bez zmeny existujúceho kódu

## Práca s viacerými databázami

Balík `slim4/root` môže výrazne zjednodušiť prácu s viacerými databázami v jednom projekte.

### Konfigurácia ciest pre rôzne databázy

```php
<?php

use Slim4\Root\Paths;

// Vytvorenie inštancie Paths pre rôzne databázy
$paths = new Paths(__DIR__, [
    // Základné cesty
    'database' => __DIR__ . '/database',
    
    // Cesty k rôznym databázam
    'database.sqlite' => __DIR__ . '/database/sqlite',
    'database.mysql' => __DIR__ . '/database/mysql',
    'database.pgsql' => __DIR__ . '/database/pgsql',
    
    // Cesty k migráciám pre rôzne databázy
    'migrations.sqlite' => __DIR__ . '/database/sqlite/migrations',
    'migrations.mysql' => __DIR__ . '/database/mysql/migrations',
    'migrations.pgsql' => __DIR__ . '/database/pgsql/migrations',
    
    // Cesty ku konfiguračným súborom
    'config.database.sqlite' => __DIR__ . '/config/database/sqlite.php',
    'config.database.mysql' => __DIR__ . '/config/database/mysql.php',
    'config.database.pgsql' => __DIR__ . '/config/database/pgsql.php',
]);
```

### Príklad použitia v aplikácii

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
        // Načítanie konfigurácie
        $configPath = $this->paths->getPaths()['config.database.' . $driver];
        $config = require $configPath;
        
        // Vytvorenie spojenia podľa drivera
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
                throw new \InvalidArgumentException("Nepodporovaný driver: {$driver}");
        }
    }
    
    public function getMigrationsPath(string $driver): string
    {
        return $this->paths->getPaths()['migrations.' . $driver];
    }
}
```

### Príklad použitia s Doctrine DBAL

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
        // Načítanie konfigurácie
        $configPath = $this->paths->getPaths()['config.database.' . $driver];
        $config = require $configPath;
        
        // Vytvorenie parametrov spojenia
        $connectionParams = [
            'driver' => $driver,
            'host' => $config['host'] ?? null,
            'dbname' => $config['database'] ?? null,
            'user' => $config['username'] ?? null,
            'password' => $config['password'] ?? null,
            'charset' => $config['charset'] ?? null,
        ];
        
        // Pre SQLite nastavíme cestu k súboru
        if ($driver === 'sqlite') {
            $dbPath = $this->paths->getPaths()['database.sqlite'] . '/database.sqlite';
            $connectionParams = [
                'driver' => 'pdo_sqlite',
                'path' => $dbPath,
            ];
        }
        
        // Vytvorenie spojenia
        return DriverManager::getConnection($connectionParams);
    }
}
```

### Výhody použitia `slim4/root` pre prácu s viacerými databázami

1. **Centralizovaná správa ciest** - Všetky cesty k databázam sú definované na jednom mieste
2. **Flexibilita** - Ľahké prepínanie medzi rôznymi databázami
3. **Čistý kód** - Žiadne hardcoded cesty v kóde
4. **Jednoduchšie testovanie** - Ľahšie mocknutie ciest v testoch

## Integrácia s frameworkami

Balík `slim4/root` môže byť integrovaný s rôznymi PHP frameworkami.

### Slim 4

```php
<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim4\Root\Paths;
use Slim4\Root\PathsInterface;
use Slim4\Root\PathsMiddleware;

// Vytvorenie inštancie Paths
$rootPath = dirname(__DIR__);
$paths = new Paths($rootPath);

// Vytvorenie kontajnera
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    PathsInterface::class => $paths,
]);
$container = $containerBuilder->build();

// Vytvorenie aplikácie
AppFactory::setContainer($container);
$app = AppFactory::create();

// Pridanie middleware
$app->add(new PathsMiddleware($paths));

// Definovanie route
$app->get('/', function ($request, $response) {
    $paths = $request->getAttribute(PathsInterface::class);
    $response->getBody()->write('Root path: ' . $paths->getRootPath());
    return $response;
});

// Spustenie aplikácie
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

    // Implementácia všetkých metód z PathsInterface
    public function getRootPath(): string
    {
        return $this->paths->getRootPath();
    }
    
    // ... ostatné metódy ...
}
```

## Testovanie

Balík `slim4/root` môže výrazne zjednodušiť testovanie aplikácie.

### Konfigurácia ciest pre testy

```php
<?php

use Slim4\Root\Paths;

// Vytvorenie inštancie Paths pre testy
$paths = new Paths(__DIR__, [
    // Základné cesty
    'tests' => __DIR__ . '/tests',
    
    // Cesty pre rôzne typy testov
    'tests.unit' => __DIR__ . '/tests/Unit',
    'tests.integration' => __DIR__ . '/tests/Integration',
    'tests.functional' => __DIR__ . '/tests/Functional',
    
    // Cesty pre fixtures
    'tests.fixtures' => __DIR__ . '/tests/Fixtures',
    
    // Cesty pre dočasné súbory
    'tests.temp' => __DIR__ . '/tests/temp',
]);
```

### Príklad použitia v testoch

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
        // Vytvorenie inštancie Paths pre testy
        $this->paths = new Paths(__DIR__ . '/../..', [
            'tests.fixtures' => __DIR__ . '/../Fixtures',
            'tests.temp' => __DIR__ . '/../temp',
        ]);
        
        // Vytvorenie dočasného adresára
        if (!is_dir($this->paths->getPaths()['tests.temp'])) {
            mkdir($this->paths->getPaths()['tests.temp'], 0755, true);
        }
    }
    
    public function testCreateUser(): void
    {
        // Vytvorenie mock repozitára
        $userRepository = $this->createMock(UserRepository::class);
        
        // Vytvorenie služby
        $userService = new UserService($userRepository, $this->paths);
        
        // Testovanie
        $user = $userService->createUser('John Doe', 'john@example.com');
        
        // Assertions
        $this->assertSame('John Doe', $user->getName());
        $this->assertSame('john@example.com', $user->getEmail());
        
        // Kontrola, či bol vytvorený log súbor
        $logPath = $this->paths->getPaths()['tests.temp'] . '/users.log';
        $this->assertFileExists($logPath);
        $this->assertStringContainsString('Created user: John Doe (john@example.com)', file_get_contents($logPath));
    }
    
    protected function tearDown(): void
    {
        // Vyčistenie dočasných súborov
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

### Výhody použitia `slim4/root` pre testovanie

1. **Konzistentné cesty** - Rovnaké cesty v produkčnom kóde aj v testoch
2. **Izolácia testov** - Ľahké oddelenie testovacích súborov od produkčných
3. **Jednoduchšie mocknutie** - Ľahšie vytvorenie mock objektov pre testy
4. **Čistejšie testy** - Žiadne hardcoded cesty v testoch

## Nasadenie (Deployment)

Balík `slim4/root` môže pomôcť aj pri nasadení aplikácie do rôznych prostredí.

### Konfigurácia ciest pre rôzne prostredia

```php
<?php

use Slim4\Root\Paths;

// Detekcia prostredia
$env = getenv('APP_ENV') ?: 'production';

// Základná cesta
$rootPath = dirname(__DIR__);

// Vytvorenie inštancie Paths podľa prostredia
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

### Príklad použitia v deployment skripte

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
        // Vyčistenie cache
        $this->clearCache();
        
        // Spustenie migrácií
        $this->runMigrations();
        
        // Vytvorenie potrebných adresárov
        $this->createDirectories();
        
        // Nastavenie oprávnení
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
        // Spustenie migrácií...
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

### Výhody použitia `slim4/root` pre nasadenie

1. **Konzistentné cesty** - Rovnaké cesty vo všetkých prostrediach
2. **Flexibilita** - Ľahké prispôsobenie ciest pre rôzne prostredia
3. **Automatizácia** - Jednoduchšia automatizácia deployment procesov
4. **Bezpečnosť** - Lepšia kontrola nad oprávneniami súborov a adresárov
