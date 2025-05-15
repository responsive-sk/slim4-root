# Testovanie s balíkom Slim4 Root

Balík `slim4-root` poskytuje nástroje pre zjednodušenie testovania aplikácií postavených na Slim 4 frameworku.

## Obsah

- [TestContainer](#testcontainer)
- [Bootstrap súbor](#bootstrap-súbor)
- [Integrácia s PHPUnit](#integrácia-s-phpunit)
- [Príklady použitia](#príklady-použitia)

## TestContainer

`TestContainer` je jednoduchý statický kontajner pre zdieľanie objektov medzi testami bez použitia globálnych premenných.

### Základné použitie

```php
use Slim4\Root\Testing\TestContainer;
use Slim4\Root\Paths;

// V bootstrap súbore
$paths = new Paths(__DIR__);
TestContainer::set(Paths::class, $paths);

// V testoch
$paths = TestContainer::get(Paths::class);
```

### Dostupné metódy

- `set(string $key, mixed $value)` - Nastaví hodnotu v kontajneri
- `get(string $key, mixed $default = null)` - Získa hodnotu z kontajnera
- `has(string $key)` - Skontroluje, či existuje hodnota v kontajneri
- `remove(string $key)` - Odstráni hodnotu z kontajnera
- `clear()` - Vyčistí všetky hodnoty v kontajneri

## Bootstrap súbor

Balík obsahuje bootstrap súbor, ktorý môžete použiť vo vašej PHPUnit konfigurácii:

```php
// tests/bootstrap.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/responsive-sk/slim4-root/src/Testing/bootstrap.php';
```

Alebo si môžete vytvoriť vlastný bootstrap súbor:

```php
// tests/bootstrap.php
require_once __DIR__ . '/../vendor/autoload.php';

use Slim4\Root\Paths;
use Slim4\Root\Testing\TestContainer;

// Vytvorenie Paths objektu
$rootPath = (string)realpath(__DIR__ . '/..');
$paths = new Paths($rootPath);

// Uloženie do TestContainer
TestContainer::set(Paths::class, $paths);

// Vytvorenie kontajnera
$containerBuilder = new \DI\ContainerBuilder();

// Pridanie Paths do kontajnera
$containerBuilder->addDefinitions([
    Paths::class => $paths,
]);

// Načítanie závislostí
if (file_exists(__DIR__ . '/../config/dependencies.php')) {
    /** @var array<string, mixed> $dependencies */
    $dependencies = require __DIR__ . '/../config/dependencies.php';
    $containerBuilder->addDefinitions($dependencies);
}

// Vytvorenie kontajnera
$container = $containerBuilder->build();

// Uloženie kontajnera do TestContainer
TestContainer::set('container', $container);
```

## Integrácia s PHPUnit

Upravte váš `phpunit.xml` súbor, aby používal bootstrap súbor:

```xml
<phpunit bootstrap="tests/bootstrap.php">
    <!-- ... -->
</phpunit>
```

## Príklady použitia

### Prístup k cestám v testoch

```php
use PHPUnit\Framework\TestCase;
use Slim4\Root\Paths;
use Slim4\Root\Testing\TestContainer;

class MyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Získanie Paths objektu z TestContainer
        $this->paths = TestContainer::get(Paths::class);
    }
    
    public function testSomething(): void
    {
        // Použitie Paths objektu
        $configPath = $this->paths->getConfigPath();
        
        // ...
    }
}
```

### Prístup ku kontajneru v testoch

```php
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Slim4\Root\Testing\TestContainer;

class MyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Získanie kontajnera z TestContainer
        $this->container = TestContainer::get('container');
    }
    
    public function testSomething(): void
    {
        // Použitie kontajnera
        $service = $this->container->get('my-service');
        
        // ...
    }
}
```

### Vytvorenie aplikácie v testoch

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
        
        // Získanie kontajnera z TestContainer
        $this->container = TestContainer::get('container');
        
        // Vytvorenie aplikácie
        $this->app = AppFactory::createFromContainer($this->container);
    }
    
    public function testSomething(): void
    {
        // Použitie aplikácie
        $this->app->get('/', function ($request, $response) {
            return $response->withJson(['status' => 'ok']);
        });
        
        // ...
    }
}
```
