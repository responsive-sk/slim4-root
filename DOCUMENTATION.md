# Slim4 Root - Dokumentácia

## Obsah

1. [Úvod](#úvod)
2. [Inštalácia](#inštalácia)
3. [Základné použitie](#základné-použitie)
4. [Konfigurácia](#konfigurácia)
5. [Auto-discovery](#auto-discovery)
6. [Validácia ciest](#validácia-ciest)
7. [Normalizácia ciest](#normalizácia-ciest)
8. [Integrácia s DI kontajnerom](#integrácia-s-di-kontajnerom)
9. [Middleware](#middleware)
10. [Integrácia s Twig](#integrácia-s-twig)
11. [Integrácia s Monolog](#integrácia-s-monolog)
12. [Dostupné metódy](#dostupné-metódy)
13. [Príklady použitia](#príklady-použitia)
14. [Riešenie problémov](#riešenie-problémov)
15. [Prispievanie](#prispievanie)

## Úvod

Slim4 Root je balík pre Slim 4 framework, ktorý poskytuje centralizovanú správu ciest relatívne k root adresáru projektu. Balík rieši problém s relatívnymi cestami (`../../../`) v kóde a poskytuje jednotný spôsob prístupu k adresárom a súborom v projekte.

### Hlavné funkcie

- **Centralizovaná správa ciest** - Všetky cesty sú definované na jednom mieste
- **Automatické objavovanie adresárovej štruktúry** - Balík dokáže automaticky objaviť bežné adresárové štruktúry
- **Validácia ciest** - Balík dokáže validovať, či cesty existujú
- **Normalizácia ciest** - Balík dokáže normalizovať cesty (napr. odstrániť koncové lomítka, nahradiť spätné lomítka za normálne)
- **Middleware** - Balík poskytuje middleware pre prístup k cestám v route handleroch
- **Integrácia s DI kontajnerom** - Balík poskytuje provider pre registráciu služieb v DI kontajneri
- **Žiadne viac relatívnych ciest** - Koniec s `../../../` v kóde, všetko je relatívne k root adresáru

## Inštalácia

Balík môžete nainštalovať pomocou Composer:

```bash
composer require responsive-sk/slim4-root
```

## Základné použitie

```php
use Slim4\Root\Paths;

// Vytvorenie inštancie Paths s povoleným auto-discovery
$paths = new Paths(__DIR__);

// Získanie ciest relatívne k root adresáru
$configPath = $paths->getConfigPath();
$viewsPath = $paths->getViewsPath();
$logsPath = $paths->getLogsPath();

// Získanie všetkých ciest naraz
$allPaths = $paths->getPaths();

// Žiadne viac ../../../ ciest!
// Namiesto:
// require_once __DIR__ . '/../../../vendor/autoload.php';
// Použite:
// require_once $paths->path('vendor/autoload.php');
// Všetko je relatívne k root adresáru projektu
```

## Konfigurácia

Balík môžete konfigurovať pomocou vlastných ciest:

```php
use Slim4\Root\Paths;

// Vytvorenie inštancie Paths s vlastnými cestami
$paths = new Paths(
    __DIR__,
    [
        'config' => __DIR__ . '/app/config',
        'views' => __DIR__ . '/app/views',
        'logs' => __DIR__ . '/app/logs',
    ],
    true, // Povoliť auto-discovery (predvolené: true)
    false // Zakázať validáciu ciest (predvolené: false)
);

// Získanie ciest
$configPath = $paths->getConfigPath(); // Vráti __DIR__ . '/app/config'
$viewsPath = $paths->getViewsPath(); // Vráti __DIR__ . '/app/views'
$logsPath = $paths->getLogsPath(); // Vráti __DIR__ . '/app/logs'
```

## Auto-discovery

Trieda `Paths` dokáže automaticky objaviť bežné adresárové štruktúry v projekte. Táto funkcia je predvolene povolená, ale môžete ju zakázať odovzdaním `false` ako tretí parameter konštruktoru.

```php
use Slim4\Root\Paths;

// S povoleným auto-discovery (predvolené)
$paths = new Paths(__DIR__);

// Bez auto-discovery
$paths = new Paths(__DIR__, [], false);
```

Proces auto-discovery hľadá nasledujúce adresáre:

- `config` - Hľadá `config`, `app/config`, `etc`
- `resources` - Hľadá `resources`, `app/resources`, `res`
- `views` - Hľadá `resources/views`, `templates`, `views`, `app/views`
- `assets` - Hľadá `resources/assets`, `assets`, `public/assets`
- `cache` - Hľadá `var/cache`, `cache`, `tmp/cache`, `storage/cache`
- `logs` - Hľadá `var/logs`, `logs`, `log`, `storage/logs`
- `public` - Hľadá `public`, `web`, `www`, `htdocs`
- `database` - Hľadá `database`, `db`, `storage/database`
- `migrations` - Hľadá `database/migrations`, `migrations`, `db/migrations`
- `storage` - Hľadá `storage`, `var`, `data`
- `tests` - Hľadá `tests`, `test`

Môžete použiť triedu `PathsDiscoverer` priamo:

```php
use Slim4\Root\PathsDiscoverer;

// Vytvorenie inštancie PathsDiscoverer
$discoverer = new PathsDiscoverer();

// Objavenie ciest
$discoveredPaths = $discoverer->discover(__DIR__);

// Použitie objavených ciest
var_dump($discoveredPaths);
```

## Validácia ciest

Trieda `Paths` dokáže validovať, či cesty existujú. Táto funkcia je predvolene zakázaná, ale môžete ju povoliť odovzdaním `true` ako štvrtý parameter konštruktoru.

```php
use Slim4\Root\Paths;

// Bez validácie (predvolené)
$paths = new Paths(__DIR__);

// S validáciou
$paths = new Paths(__DIR__, [], true, true);
```

Ak je validácia povolená a cesta neexistuje, vyhodí sa výnimka `InvalidPathException`:

```php
use Slim4\Root\Paths;
use Slim4\Root\Exception\InvalidPathException;

try {
    $paths = new Paths(__DIR__, [], true, true);
} catch (InvalidPathException $e) {
    echo $e->getMessage(); // "Configured path for 'views' is not a valid directory: /path/to/views"
}
```

Môžete použiť triedu `PathsValidator` priamo:

```php
use Slim4\Root\PathsValidator;
use Slim4\Root\Exception\InvalidPathException;

// Vytvorenie inštancie PathsValidator
$validator = new PathsValidator();

// Validácia ciest
try {
    $validator->validate([
        'config' => __DIR__ . '/config',
        'views' => __DIR__ . '/views',
    ], true); // Striktná validácia (vyhodí výnimku, ak cesta neexistuje)
} catch (InvalidPathException $e) {
    echo $e->getMessage();
}
```

## Normalizácia ciest

Balík poskytuje triedu `PathsNormalizer` pre normalizáciu ciest:

```php
use Slim4\Root\PathsNormalizer;

// Vytvorenie inštancie PathsNormalizer
$normalizer = new PathsNormalizer();

// Normalizácia ciest
$normalizedPath = $normalizer->normalize('/var/www/project/');
// Vráti: '/var/www/project'

// Normalizácia Windows ciest (ak pracujete na Windows)
$windowsPath = $normalizer->normalize('D:\\projekty\\slim4\\');
// Vráti: 'D:/projekty/slim4'
```

## Integrácia s DI kontajnerom

Balík poskytuje triedu `PathsProvider` pre registráciu služieb v DI kontajneri:

```php
use Slim4\Root\PathsProvider;
use DI\ContainerBuilder;

// Vytvorenie kontajnera
$containerBuilder = new ContainerBuilder();

// Registrácia služieb pre cesty
$rootPath = dirname(__DIR__);
PathsProvider::register(
    $containerBuilder->build(),
    $rootPath,
    [], // Vlastné cesty
    true, // Povoliť auto-discovery
    false // Zakázať validáciu ciest
);

// Získanie ciest z kontajnera
$paths = $container->get(Slim4\Root\PathsInterface::class);
```

## Middleware

Balík poskytuje middleware `PathsMiddleware` pre prístup k cestám v route handleroch:

```php
use Slim4\Root\PathsMiddleware;
use Slim4\Root\PathsInterface;

// Pridanie middleware
$app->add(new PathsMiddleware($paths));

// Použitie ciest v route handleroch
$app->get('/', function (Request $request, Response $response) {
    // Získanie ciest z atribútov požiadavky
    $paths = $request->getAttribute(PathsInterface::class);

    // Použitie ciest
    $configPath = $paths->getConfigPath();

    // ...

    return $response;
});
```

## Integrácia s Twig

Balík môžete integrovať s Twig:

```php
use Slim4\Root\PathsInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Vytvorenie Twig prostredia
$paths = $container->get(PathsInterface::class);
$loader = new FilesystemLoader($paths->getViewsPath());
$twig = new Environment($loader, [
    'cache' => $paths->getCachePath() . '/twig',
    'auto_reload' => true,
]);

// Pridanie ciest do globálnych premenných Twig
$twig->addGlobal('paths', $paths);
```

V Twig šablónach:

```twig
{# Použitie ciest v šablónach #}
<link rel="stylesheet" href="{{ paths.getPublicPath() }}/css/style.css">
<script src="{{ paths.getPublicPath() }}/js/app.js"></script>
```

## Integrácia s Monolog

Balík môžete integrovať s Monolog:

```php
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim4\Root\PathsInterface;

// Vytvorenie loggera
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

## Dostupné metódy

### PathsInterface

- `getRootPath()` - Získanie root cesty projektu
- `getConfigPath()` - Získanie cesty ku konfiguračným súborom
- `getResourcesPath()` - Získanie cesty k zdrojom
- `getViewsPath()` - Získanie cesty k pohľadom
- `getAssetsPath()` - Získanie cesty k assets
- `getCachePath()` - Získanie cesty k cache
- `getLogsPath()` - Získanie cesty k logom
- `getPublicPath()` - Získanie cesty k verejným súborom
- `getDatabasePath()` - Získanie cesty k databáze
- `getMigrationsPath()` - Získanie cesty k migráciám
- `getStoragePath()` - Získanie cesty k úložisku
- `getTestsPath()` - Získanie cesty k testom
- `path(string $path)` - Získanie cesty relatívne k root ceste
- `getPaths()` - Získanie všetkých ciest ako asociatívne pole

### PathsDiscoverer

- `discover(string $rootPath)` - Objavenie ciest v danej root ceste

### PathsValidator

- `validate(array $paths, bool $strict)` - Validácia ciest

### PathsNormalizer

- `normalize(string $path)` - Normalizácia cesty

## Príklady použitia

### Použitie s Slim 4 aplikáciou

```php
<?php

use Slim\Factory\AppFactory;
use Slim4\Root\Paths;
use Slim4\Root\PathsMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

// Vytvorenie inštancie Paths
$rootPath = dirname(__DIR__);
$paths = new Paths($rootPath);

// Vytvorenie aplikácie
$app = AppFactory::create();

// Pridanie middleware
$app->add(new PathsMiddleware($paths));

// Definovanie route
$app->get('/', function ($request, $response) {
    $paths = $request->getAttribute(Slim4\Root\PathsInterface::class);
    $response->getBody()->write('Root path: ' . $paths->getRootPath());
    return $response;
});

// Spustenie aplikácie
$app->run();
```

### Použitie s vlastnou adresárovou štruktúrou

```php
<?php

use Slim4\Root\Paths;

// Vlastná adresárová štruktúra
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

// Použitie ciest
$configPath = $paths->getConfigPath();
$viewsPath = $paths->getViewsPath();
$logsPath = $paths->getLogsPath();
```

### Použitie s PHP-DI

```php
<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim4\Root\PathsProvider;

// Vytvorenie kontajnera
$containerBuilder = new ContainerBuilder();
$container = $containerBuilder->build();

// Registrácia služieb pre cesty
$rootPath = dirname(__DIR__);
PathsProvider::register($container, $rootPath);

// Vytvorenie aplikácie
AppFactory::setContainer($container);
$app = AppFactory::create();

// Použitie ciest z kontajnera
$paths = $container->get(Slim4\Root\PathsInterface::class);
```

## Integrácia s Hexagonálnou architektúrou a DDD

Balík `slim4/root` je ideálny pre projekty používajúce Hexagonálnu architektúru (HEXA) a Domain-Driven Design (DDD), ktoré sú čoraz populárnejšie v PHP ekosystéme.

### Výhody pre Hexagonálnu architektúru

V hexagonálnej architektúre (známej aj ako Ports & Adapters) je aplikácia rozdelená na vnútornú doménu a vonkajšie adaptery. Balík `slim4/root` pomáha:

1. **Jasne definovať štruktúru projektu** - Umožňuje konzistentný prístup k rôznym častiam aplikácie
2. **Oddelit' doménu od infraštruktúry** - Cesty k doméne, aplikácii a infraštruktúre môžu byť jasne definované
3. **Zjednodušiť testovanie** - Ľahšie nastavenie testovacích ciest a fixtur

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

// Použitie ciest v aplikácii
$domainPath = $paths->getPaths()['domain'];
$portsPath = $paths->getPaths()['ports'];
```

### Výhody pre Domain-Driven Design (DDD)

Pre projekty používajúce DDD, balík `slim4/root` pomáha:

1. **Organizovať Bounded Contexts** - Jasne definovať cesty k rôznym bounded kontextom
2. **Spravovať doménové modely** - Ľahší prístup k entitám, value objektom a aggregátom
3. **Podporovať vrstvovú architektúru** - Jasne oddeliť doménu, aplikáciu a infraštruktúru

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
]);

// Použitie ciest v aplikácii
$userContextPath = $paths->getPaths()['contexts.user'];
$userEntitiesPath = $paths->getPaths()['contexts.user.domain.entities'];
```

### Prístup k databáze pre každý port

Jednou z výhod hexagonálnej architektúry je možnosť mať rôzne implementácie portov pre rôzne databázy. Balík `slim4/root` vám pomôže spravovať cesty k týmto implementáciám:

```php
<?php

use Slim4\Root\Paths;

// Vytvorenie inštancie Paths pre rôzne databázové implementácie
$paths = new Paths(__DIR__, [
    // Základné cesty
    'domain' => __DIR__ . '/src/Domain',
    'application' => __DIR__ . '/src/Application',
    'infrastructure' => __DIR__ . '/src/Infrastructure',

    // Porty (rozhrania)
    'ports.repositories' => __DIR__ . '/src/Application/Ports/Repositories',

    // Adaptery pre rôzne databázy
    'adapters.repositories.mysql' => __DIR__ . '/src/Infrastructure/Adapters/Repositories/MySQL',
    'adapters.repositories.pgsql' => __DIR__ . '/src/Infrastructure/Adapters/Repositories/PostgreSQL',
    'adapters.repositories.mongodb' => __DIR__ . '/src/Infrastructure/Adapters/Repositories/MongoDB',
    'adapters.repositories.redis' => __DIR__ . '/src/Infrastructure/Adapters/Repositories/Redis',

    // Konfigurácia pre rôzne databázy
    'config.mysql' => __DIR__ . '/config/databases/mysql.php',
    'config.pgsql' => __DIR__ . '/config/databases/pgsql.php',
    'config.mongodb' => __DIR__ . '/config/databases/mongodb.php',
    'config.redis' => __DIR__ . '/config/databases/redis.php',
]);

// Použitie v továrni na vytvorenie repozitára
class RepositoryFactory
{
    private $paths;

    public function __construct(Slim4\Root\PathsInterface $paths)
    {
        $this->paths = $paths;
    }

    public function createUserRepository(string $driver = 'mysql')
    {
        // Získanie cesty k implementácii repozitára podľa drivera
        $adapterPath = $this->paths->getPaths()['adapters.repositories.' . $driver];
        $configPath = $this->paths->getPaths()['config.' . $driver];

        // Načítanie konfigurácie
        $config = require $configPath;

        // Vytvorenie a vrátenie správneho repozitára
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
                throw new \InvalidArgumentException("Nepodporovaný driver: {$driver}");
        }
    }
}
```

Tento prístup vám umožňuje:

1. **Flexibilitu pri výbere databázy** - Jednoducho prepnúť medzi rôznymi databázami bez zmeny biznis logiky
2. **Čistú doménu** - Doména nemusí vedieť o konkrétnej databáze
3. **Jednoduché testovanie** - Ľahšie vytváranie mock objektov a testovacích implementácií
4. **Škálovateľnosť** - Možnosť použiť rôzne databázy pre rôzne časti aplikácie

## Pokročilé použitie

### Cesty k rôznym databázam

Jednou z veľkých výhod balíka `slim4/root` je možnosť jednoduchého prístupu k rôznym databázam a ich konfiguračným súborom. Napríklad:

```php
<?php

use Slim4\Root\Paths;

// Vytvorenie inštancie Paths
$paths = new Paths(__DIR__, [
    // Štandardné cesty
    'database' => __DIR__ . '/database',

    // Cesty k rôznym databázam
    'database.sqlite' => __DIR__ . '/database/sqlite',
    'database.mysql' => __DIR__ . '/database/mysql',
    'database.pgsql' => __DIR__ . '/database/pgsql',

    // Cesty k migráciám pre rôzne databázy
    'migrations.sqlite' => __DIR__ . '/database/sqlite/migrations',
    'migrations.mysql' => __DIR__ . '/database/mysql/migrations',
    'migrations.pgsql' => __DIR__ . '/database/pgsql/migrations',

    // Cesty k seedom pre rôzne databázy
    'seeds.sqlite' => __DIR__ . '/database/sqlite/seeds',
    'seeds.mysql' => __DIR__ . '/database/mysql/seeds',
    'seeds.pgsql' => __DIR__ . '/database/pgsql/seeds',
]);

// Použitie ciest k rôznym databázam
$sqlitePath = $paths->getPaths()['database.sqlite'];
$mysqlPath = $paths->getPaths()['database.mysql'];
$pgsqlPath = $paths->getPaths()['database.pgsql'];

// Použitie ciest k migráciám pre rôzne databázy
$sqliteMigrationsPath = $paths->getPaths()['migrations.sqlite'];
$mysqlMigrationsPath = $paths->getPaths()['migrations.mysql'];
$pgsqlMigrationsPath = $paths->getPaths()['migrations.pgsql'];

// Prístup ku konfiguračným súborom databáz
$sqliteConfigPath = $paths->path('config/database/sqlite.php');
$mysqlConfigPath = $paths->path('config/database/mysql.php');
$pgsqlConfigPath = $paths->path('config/database/pgsql.php');
```

Toto je obzvlášť užitočné v projektoch, ktoré potrebujú pracovať s viacerými databázami súčasne alebo podporovať rôzne databázové systémy.

### Integrácia s databázovými klientmi

Balík `slim4/root` môže byť použitý na konfiguráciu rôznych databázových klientov:

```php
<?php

use Slim4\Root\Paths;

// Vytvorenie inštancie Paths
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

// Použitie s PDO
$sqlitePdo = new PDO('sqlite:' . $sqliteConfig['database']);

// Použitie s Doctrine DBAL
$connectionParams = [
    'driver' => $mysqlConfig['driver'],
    'host' => $mysqlConfig['host'],
    'dbname' => $mysqlConfig['database'],
    'user' => $mysqlConfig['username'],
    'password' => $mysqlConfig['password'],
    'charset' => $mysqlConfig['charset'],
];
$connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);

// Použitie s Eloquent ORM
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($pgsqlConfig, 'pgsql');
$capsule->setAsGlobal();
$capsule->bootEloquent();
```

### Cesty k rôznym cache systémom

Podobný prístup môžete použiť aj pre rôzne cache systémy:

```php
<?php

use Slim4\Root\Paths;

// Vytvorenie inštancie Paths
$paths = new Paths(__DIR__, [
    // Štandardné cesty
    'cache' => __DIR__ . '/var/cache',

    // Cesty k rôznym cache systémom
    'cache.redis' => __DIR__ . '/var/cache/redis',
    'cache.memcached' => __DIR__ . '/var/cache/memcached',
    'cache.file' => __DIR__ . '/var/cache/file',
    'cache.apc' => __DIR__ . '/var/cache/apc',
]);

// Použitie ciest k rôznym cache systémom
$redisPath = $paths->getPaths()['cache.redis'];
$memcachedPath = $paths->getPaths()['cache.memcached'];
$filePath = $paths->getPaths()['cache.file'];
$apcPath = $paths->getPaths()['cache.apc'];
```

## Use-cases pre rôzne frameworky

### Laravel

Aj keď Laravel má vlastný systém pre správu ciest, `slim4/root` môže byť užitočný v Laravel projektoch, ktoré majú netradičnú štruktúru alebo potrebujú prístup k cestám mimo Laravel aplikácie.

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
                    // Vlastné cesty pre Laravel
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
                false, // Zakázať auto-discovery, použijeme Laravel cesty
                false  // Zakázať validáciu ciest
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

Potom v `config/app.php` pridajte provider:

```php
'providers' => [
    // ...
    App\Providers\PathServiceProvider::class,
],
```

A použite v kontroleri:

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
        // Použitie ciest
        $configPath = $this->paths->getConfigPath();
        $viewsPath = $this->paths->getViewsPath();

        // Prístup k súboru relatívne k root adresáru
        $filePath = $this->paths->path('some/custom/directory/file.txt');

        return view('welcome', [
            'configPath' => $configPath,
            'viewsPath' => $viewsPath,
            'filePath' => $filePath,
        ]);
    }
}
```

### Slim 4 s Twig a Monolog

Kompletný príklad integrácie `slim4/root` so Slim 4, Twig a Monolog:

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

// Vytvorenie kontajnera
$containerBuilder = new ContainerBuilder();

// Definícia služieb
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

        // Pridanie ciest do globálnych premenných Twig
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

// Vytvorenie kontajnera
$container = $containerBuilder->build();

// Vytvorenie aplikácie
AppFactory::setContainer($container);
$app = AppFactory::create();

// Pridanie middleware
$app->add(TwigMiddleware::createFromContainer($app, Twig::class));
$app->add(new PathsMiddleware($container->get(PathsInterface::class)));

// Definícia routes
$app->get('/', function ($request, $response, $args) use ($container) {
    $paths = $request->getAttribute(PathsInterface::class);
    $logger = $container->get(Logger::class);

    // Logujeme prístup
    $logger->info('Home page accessed');

    // Renderujeme šablónu
    return $container->get(Twig::class)->render($response, 'home.twig', [
        'title' => 'Slim4 Root Example',
        'rootPath' => $paths->getRootPath(),
        'configPath' => $paths->getConfigPath(),
        'viewsPath' => $paths->getViewsPath(),
    ]);
});

// Spustenie aplikácie
$app->run();
```

A šablóna `views/home.twig`:

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

Aj keď Symfony má vlastný systém pre správu ciest, `slim4/root` môže byť užitočný v Symfony projektoch, ktoré potrebujú prístup k cestám mimo štandardných Symfony konvencií.

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
                // Vlastné cesty pre Symfony
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
            false, // Zakázať auto-discovery, použijeme Symfony cesty
            false  // Zakázať validáciu ciest
        );
    }

    // Implementácia všetkých metód z PathsInterface
    public function getRootPath(): string
    {
        return $this->paths->getRootPath();
    }

    public function getConfigPath(): string
    {
        return $this->paths->getConfigPath();
    }

    // ... ostatné metódy ...

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

Registrácia služby v `config/services.yaml`:

```yaml
services:
    # ...
    App\Service\PathsService:
        arguments:
            - '@kernel'
            - '@parameter_bag'

    # Alias pre rozhranie
    Slim4\Root\PathsInterface:
        alias: App\Service\PathsService
```

A použitie v kontroleri:

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
        // Použitie ciest
        $configPath = $this->paths->getConfigPath();
        $viewsPath = $this->paths->getViewsPath();

        // Prístup k súboru relatívne k root adresáru
        $filePath = $this->paths->path('some/custom/directory/file.txt');

        return $this->render('home/index.html.twig', [
            'configPath' => $configPath,
            'viewsPath' => $viewsPath,
            'filePath' => $filePath,
        ]);
    }
}
```

## Riešenie problémov

### Cesta neexistuje

Ak je validácia ciest povolená a cesta neexistuje, vyhodí sa výnimka `InvalidPathException`. Riešením je buď vytvoriť chýbajúci adresár, alebo zakázať validáciu ciest:

```php
use Slim4\Root\Paths;

// Zakázanie validácie ciest
$paths = new Paths(__DIR__, [], true, false);
```

### Problémy s autoloadingom

Ak máte problémy s autoloadingom, uistite sa, že máte správne nastavený autoloading v `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "Slim4\\Root\\": "src/"
        }
    }
}
```

A potom spustite:

```bash
composer dump-autoload
```

## Príklady použitia

### Jednoduchý príklad

Pre jednoduchý príklad použitia balíka `slim4/root` v bežnom Slim 4 projekte, pozrite si súbor [SIMPLE-EXAMPLE.md](SIMPLE-EXAMPLE.md).

### Integrácia s template enginami

Pre príklady integrácie balíka `slim4/root` s populárnymi PHP template enginami (Blade, Plates, Volt, Twig, Latte, Smarty), pozrite si súbor [TEMPLATE-ENGINES.md](TEMPLATE-ENGINES.md).

### Pokročilé prípady použitia

Pre podrobné prípady použitia balíka `slim4/root` v rôznych scenároch a architektúrach, pozrite si súbor [USE-CASES.md](USE-CASES.md).

## Licencia

Balík `slim4/root` je licencovaný pod MIT licenciou. Táto licencia je veľmi liberálna a umožňuje používať, modifikovať a distribuovať kód bez väčších obmedzení.

### Kompatibilita s inými frameworkami

Rôzne PHP frameworky používajú rôzne licencie:

- **Slim 4**: MIT licencia
- **Laravel**: MIT licencia
- **Symfony**: MIT licencia
- **Laminas** (bývalý Zend Framework): BSD 3-Clause licencia

Balík `slim4/root` je kompatibilný so všetkými týmito licenciami, čo znamená, že ho môžete použiť v projektoch založených na ktorýchkoľvek z týchto frameworkov.

Ak by ste chceli vytvoriť podobný balík pre iný framework, odporúčame použiť licenciu, ktorá je kompatibilná s licenciou daného frameworku.

## Prispievanie

Príspevky sú vítané a budú plne ocenené. Akceptujeme príspevky prostredníctvom Pull Requests na [Github](https://github.com/responsive-sk/slim4-root).

### Pull Requests

- **[PSR-12 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-12-extended-coding-style-guide.md)** - Najjednoduchší spôsob, ako aplikovať konvencie, je nainštalovať [PHP Code Sniffer](http://pear.php.net/package/PHP_CodeSniffer).
- **Pridajte testy!** - Váš patch nebude akceptovaný, ak nebude mať testy.
- **Dokumentujte akúkoľvek zmenu v správaní** - Uistite sa, že `README.md` a akákoľvek iná relevantná dokumentácia sú aktuálne.
- **Zvážte náš cyklus vydávania** - Snažíme sa dodržiavať [SemVer v2.0.0](http://semver.org/). Náhodné porušenie verejných API nie je možnosťou.
- **Vytvorte vetvy funkcií** - Nežiadajte nás, aby sme ťahali z vašej master vetvy.
- **Jeden pull request na funkciu** - Ak chcete urobiť viac ako jednu vec, pošlite viacero pull requestov.
- **Pošlite koherentnú históriu** - Uistite sa, že každý jednotlivý commit vo vašom pull requeste je zmysluplný. Ak ste museli urobiť viacero priebežných commitov počas vývoja, prosím [squashujte ich](http://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages) pred odoslaním.

### Spustenie testov

```bash
composer test
```

### Spustenie PHP Code Sniffer

```bash
composer check-style
composer fix-style
```

### Spustenie PHPStan

```bash
composer phpstan
```

**Šťastné kódovanie!**
