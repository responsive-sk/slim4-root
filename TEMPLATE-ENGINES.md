# Slim4 Root - Integrácia s template enginami

Tento dokument obsahuje príklady integrácie balíka `slim4/root` s populárnymi PHP template enginami.

## Obsah

1. [Blade (Laravel)](#blade-laravel)
2. [Plates](#plates)
3. [Volt (Phalcon)](#volt-phalcon)
4. [Twig](#twig)
5. [Latte](#latte)
6. [Smarty](#smarty)

## Blade (Laravel)

[Blade](https://laravel.com/docs/blade) je populárny template engine z frameworku Laravel, ktorý môžete použiť aj v Slim 4 projektoch pomocou balíka [jenssegers/blade](https://github.com/jenssegers/blade).

### Inštalácia

```bash
composer require jenssegers/blade
```

### Integrácia s `slim4/root`

```php
<?php

namespace App\Views;

use Jenssegers\Blade\Blade;
use Slim4\Root\PathsInterface;

class BladeRenderer
{
    private Blade $blade;
    
    public function __construct(PathsInterface $paths)
    {
        // Vytvorenie inštancie Blade s cestami z Paths
        $viewsPath = $paths->getViewsPath();
        $cachePath = $paths->getCachePath() . '/views';
        
        // Vytvorenie cache adresára, ak neexistuje
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
        
        $this->blade = new Blade($viewsPath, $cachePath);
        
        // Pridanie globálnych dát
        $this->blade->share('paths', $paths);
    }
    
    public function render(string $template, array $data = []): string
    {
        return $this->blade->render($template, $data);
    }
    
    public function addDirective(string $name, callable $handler): void
    {
        $this->blade->directive($name, $handler);
    }
    
    public function getBlade(): Blade
    {
        return $this->blade;
    }
}
```

### Registrácia v DI kontajneri

```php
<?php

use App\Views\BladeRenderer;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim4\Root\PathsInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Registrácia BladeRenderer do kontajnera
        BladeRenderer::class => function (ContainerInterface $c) {
            $paths = $c->get(PathsInterface::class);
            return new BladeRenderer($paths);
        },
    ]);
};
```

### Použitie v kontroleri

```php
<?php

namespace App\Controllers;

use App\Views\BladeRenderer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController
{
    private BladeRenderer $blade;
    
    public function __construct(BladeRenderer $blade)
    {
        $this->blade = $blade;
    }
    
    public function index(Request $request, Response $response): Response
    {
        $html = $this->blade->render('home', [
            'title' => 'Domovská stránka',
            'content' => 'Vitajte na našej stránke!',
        ]);
        
        $response->getBody()->write($html);
        
        return $response;
    }
}
```

### Príklad Blade šablóny

```blade
<!-- views/home.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="{{ $paths->getPublicPath() }}/css/style.css">
</head>
<body>
    <h1>{{ $title }}</h1>
    
    <div class="content">
        {{ $content }}
    </div>
    
    <footer>
        <p>Cesta k views: {{ $paths->getViewsPath() }}</p>
    </footer>
    
    <script src="{{ $paths->getPublicPath() }}/js/app.js"></script>
</body>
</html>
```

### Vytvorenie vlastnej Blade direktívy

```php
<?php

// Pridanie vlastnej direktívy
$blade->addDirective('pathto', function ($expression) {
    return "<?php echo \$paths->path($expression); ?>";
});
```

Potom môžete použiť direktívu v šablóne:

```blade
<img src="@pathto('public/images/logo.png')" alt="Logo">
```

## Plates

[Plates](https://platesphp.com/) je moderný, natívny PHP template engine, ktorý je jednoduchý a nemá žiadne závislosti.

### Inštalácia

```bash
composer require league/plates
```

### Integrácia s `slim4/root`

```php
<?php

namespace App\Views;

use League\Plates\Engine;
use Slim4\Root\PathsInterface;

class PlatesRenderer
{
    private Engine $plates;
    private PathsInterface $paths;
    
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
        
        // Vytvorenie inštancie Plates s cestou z Paths
        $viewsPath = $paths->getViewsPath();
        $this->plates = new Engine($viewsPath);
        
        // Pridanie globálnych dát
        $this->plates->addData(['paths' => $paths]);
        
        // Registrácia adresárov s šablónami
        $this->plates->addFolder('layouts', $viewsPath . '/layouts');
        $this->plates->addFolder('partials', $viewsPath . '/partials');
    }
    
    public function render(string $template, array $data = []): string
    {
        return $this->plates->render($template, $data);
    }
    
    public function addFunction(string $name, callable $callback): void
    {
        $this->plates->registerFunction($name, $callback);
    }
    
    public function getPlates(): Engine
    {
        return $this->plates;
    }
}
```

### Registrácia v DI kontajneri

```php
<?php

use App\Views\PlatesRenderer;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim4\Root\PathsInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Registrácia PlatesRenderer do kontajnera
        PlatesRenderer::class => function (ContainerInterface $c) {
            $paths = $c->get(PathsInterface::class);
            return new PlatesRenderer($paths);
        },
    ]);
};
```

### Použitie v kontroleri

```php
<?php

namespace App\Controllers;

use App\Views\PlatesRenderer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController
{
    private PlatesRenderer $plates;
    
    public function __construct(PlatesRenderer $plates)
    {
        $this->plates = $plates;
    }
    
    public function index(Request $request, Response $response): Response
    {
        $html = $this->plates->render('home', [
            'title' => 'Domovská stránka',
            'content' => 'Vitajte na našej stránke!',
        ]);
        
        $response->getBody()->write($html);
        
        return $response;
    }
}
```

### Príklad Plates šablóny

```php
<!-- views/home.php -->
<?php $this->layout('layouts::default', ['title' => $title]) ?>

<h1><?= $this->e($title) ?></h1>

<div class="content">
    <?= $this->e($content) ?>
</div>

<footer>
    <p>Cesta k views: <?= $this->e($paths->getViewsPath()) ?></p>
</footer>
```

```php
<!-- views/layouts/default.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= $this->e($title) ?></title>
    <link rel="stylesheet" href="<?= $this->e($paths->getPublicPath()) ?>/css/style.css">
</head>
<body>
    <?= $this->section('content') ?>
    
    <script src="<?= $this->e($paths->getPublicPath()) ?>/js/app.js"></script>
</body>
</html>
```

### Vytvorenie vlastnej Plates funkcie

```php
<?php

// Pridanie vlastnej funkcie
$plates->addFunction('pathTo', function ($path) use ($paths) {
    return $paths->path($path);
});
```

Potom môžete použiť funkciu v šablóne:

```php
<img src="<?= $this->pathTo('public/images/logo.png') ?>" alt="Logo">
```

## Volt (Phalcon)

[Volt](https://docs.phalcon.io/4.0/en/volt) je rýchly a flexibilný template engine pre PHP, ktorý je súčasťou frameworku Phalcon. Môžete ho použiť aj samostatne pomocou balíka [phalcon/volt](https://github.com/phalcon/volt).

### Inštalácia

```bash
composer require phalcon/volt
```

### Integrácia s `slim4/root`

```php
<?php

namespace App\Views;

use Phalcon\Mvc\View\Engine\Volt\Compiler;
use Slim4\Root\PathsInterface;

class VoltRenderer
{
    private Compiler $volt;
    private PathsInterface $paths;
    
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
        
        // Vytvorenie inštancie Volt s cestami z Paths
        $this->volt = new Compiler();
        
        // Nastavenie cesty pre cache
        $cachePath = $paths->getCachePath() . '/volt';
        
        // Vytvorenie cache adresára, ak neexistuje
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
        
        $this->volt->setOptions([
            'compiledPath' => $cachePath,
            'compiledSeparator' => '_',
        ]);
        
        // Registrácia funkcií
        $this->registerFunctions();
    }
    
    private function registerFunctions(): void
    {
        // Pridanie funkcie pre prístup k cestám
        $paths = $this->paths;
        $this->volt->addFunction('path', function ($resolvedArgs, $exprArgs) use ($paths) {
            return '$paths->path(' . $resolvedArgs . ')';
        });
        
        // Pridanie funkcií pre prístup k štandardným cestám
        $this->volt->addFunction('getRootPath', function () use ($paths) {
            return '$paths->getRootPath()';
        });
        
        $this->volt->addFunction('getPublicPath', function () use ($paths) {
            return '$paths->getPublicPath()';
        });
        
        $this->volt->addFunction('getViewsPath', function () use ($paths) {
            return '$paths->getViewsPath()';
        });
    }
    
    public function render(string $template, array $data = []): string
    {
        // Pridanie paths do dát
        $data['paths'] = $this->paths;
        
        // Získanie cesty k šablóne
        $viewsPath = $this->paths->getViewsPath();
        $templatePath = $viewsPath . '/' . $template . '.volt';
        
        // Kompilácia šablóny
        $compiledTemplate = $this->volt->compile($templatePath);
        
        // Extrakcia dát pre šablónu
        extract($data);
        
        // Zachytenie výstupu
        ob_start();
        include $compiledTemplate;
        return ob_get_clean();
    }
    
    public function getVolt(): Compiler
    {
        return $this->volt;
    }
}
```

### Registrácia v DI kontajneri

```php
<?php

use App\Views\VoltRenderer;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim4\Root\PathsInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Registrácia VoltRenderer do kontajnera
        VoltRenderer::class => function (ContainerInterface $c) {
            $paths = $c->get(PathsInterface::class);
            return new VoltRenderer($paths);
        },
    ]);
};
```

### Použitie v kontroleri

```php
<?php

namespace App\Controllers;

use App\Views\VoltRenderer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController
{
    private VoltRenderer $volt;
    
    public function __construct(VoltRenderer $volt)
    {
        $this->volt = $volt;
    }
    
    public function index(Request $request, Response $response): Response
    {
        $html = $this->volt->render('home', [
            'title' => 'Domovská stránka',
            'content' => 'Vitajte na našej stránke!',
        ]);
        
        $response->getBody()->write($html);
        
        return $response;
    }
}
```

### Príklad Volt šablóny

```volt
<!-- views/home.volt -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ title }}</title>
    <link rel="stylesheet" href="{{ getPublicPath() }}/css/style.css">
</head>
<body>
    <h1>{{ title }}</h1>
    
    <div class="content">
        {{ content }}
    </div>
    
    <footer>
        <p>Cesta k views: {{ getViewsPath() }}</p>
    </footer>
    
    <script src="{{ getPublicPath() }}/js/app.js"></script>
</body>
</html>
```

## Twig

[Twig](https://twig.symfony.com/) je flexibilný, rýchly a bezpečný template engine pre PHP.

### Inštalácia

```bash
composer require slim/twig-view
```

### Integrácia s `slim4/root`

```php
<?php

namespace App\Views;

use Slim\Views\Twig;
use Slim4\Root\PathsInterface;
use Twig\TwigFunction;

class TwigRenderer
{
    private Twig $twig;
    
    public function __construct(PathsInterface $paths)
    {
        // Vytvorenie inštancie Twig s cestami z Paths
        $viewsPath = $paths->getViewsPath();
        $cachePath = $paths->getCachePath() . '/twig';
        
        // Vytvorenie cache adresára, ak neexistuje
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
        
        $this->twig = Twig::create($viewsPath, [
            'cache' => $cachePath,
            'auto_reload' => true,
        ]);
        
        // Pridanie globálnych dát
        $this->twig->getEnvironment()->addGlobal('paths', $paths);
        
        // Registrácia funkcií
        $this->registerFunctions($paths);
    }
    
    private function registerFunctions(PathsInterface $paths): void
    {
        // Pridanie funkcie pre prístup k cestám
        $this->twig->getEnvironment()->addFunction(new TwigFunction('path', function (string $path) use ($paths) {
            return $paths->path($path);
        }));
    }
    
    public function render(string $template, array $data = []): string
    {
        return $this->twig->fetch($template . '.twig', $data);
    }
    
    public function getTwig(): Twig
    {
        return $this->twig;
    }
}
```

### Registrácia v DI kontajneri

```php
<?php

use App\Views\TwigRenderer;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim4\Root\PathsInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Registrácia TwigRenderer do kontajnera
        TwigRenderer::class => function (ContainerInterface $c) {
            $paths = $c->get(PathsInterface::class);
            return new TwigRenderer($paths);
        },
    ]);
};
```

### Použitie v kontroleri

```php
<?php

namespace App\Controllers;

use App\Views\TwigRenderer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController
{
    private TwigRenderer $twig;
    
    public function __construct(TwigRenderer $twig)
    {
        $this->twig = $twig;
    }
    
    public function index(Request $request, Response $response): Response
    {
        $html = $this->twig->render('home', [
            'title' => 'Domovská stránka',
            'content' => 'Vitajte na našej stránke!',
        ]);
        
        $response->getBody()->write($html);
        
        return $response;
    }
}
```

### Príklad Twig šablóny

```twig
<!-- views/home.twig -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ title }}</title>
    <link rel="stylesheet" href="{{ paths.getPublicPath() }}/css/style.css">
</head>
<body>
    <h1>{{ title }}</h1>
    
    <div class="content">
        {{ content }}
    </div>
    
    <footer>
        <p>Cesta k views: {{ paths.getViewsPath() }}</p>
    </footer>
    
    <script src="{{ paths.getPublicPath() }}/js/app.js"></script>
</body>
</html>
```

## Latte

[Latte](https://latte.nette.org/) je bezpečný a intuitívny template engine pre PHP.

### Inštalácia

```bash
composer require latte/latte
```

### Integrácia s `slim4/root`

```php
<?php

namespace App\Views;

use Latte\Engine;
use Slim4\Root\PathsInterface;

class LatteRenderer
{
    private Engine $latte;
    private PathsInterface $paths;
    
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
        
        // Vytvorenie inštancie Latte
        $this->latte = new Engine();
        
        // Nastavenie cesty pre cache
        $cachePath = $paths->getCachePath() . '/latte';
        
        // Vytvorenie cache adresára, ak neexistuje
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
        
        $this->latte->setTempDirectory($cachePath);
        
        // Pridanie globálnych dát
        $this->latte->addProvider('paths', $paths);
        
        // Registrácia funkcií
        $this->registerFunctions();
    }
    
    private function registerFunctions(): void
    {
        // Pridanie funkcie pre prístup k cestám
        $paths = $this->paths;
        $this->latte->addFunction('path', function (string $path) use ($paths) {
            return $paths->path($path);
        });
    }
    
    public function render(string $template, array $data = []): string
    {
        // Pridanie paths do dát
        $data['paths'] = $this->paths;
        
        // Získanie cesty k šablóne
        $viewsPath = $this->paths->getViewsPath();
        $templatePath = $viewsPath . '/' . $template . '.latte';
        
        // Renderovanie šablóny
        return $this->latte->renderToString($templatePath, $data);
    }
    
    public function getLatte(): Engine
    {
        return $this->latte;
    }
}
```

### Registrácia v DI kontajneri

```php
<?php

use App\Views\LatteRenderer;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim4\Root\PathsInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Registrácia LatteRenderer do kontajnera
        LatteRenderer::class => function (ContainerInterface $c) {
            $paths = $c->get(PathsInterface::class);
            return new LatteRenderer($paths);
        },
    ]);
};
```

### Použitie v kontroleri

```php
<?php

namespace App\Controllers;

use App\Views\LatteRenderer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController
{
    private LatteRenderer $latte;
    
    public function __construct(LatteRenderer $latte)
    {
        $this->latte = $latte;
    }
    
    public function index(Request $request, Response $response): Response
    {
        $html = $this->latte->render('home', [
            'title' => 'Domovská stránka',
            'content' => 'Vitajte na našej stránke!',
        ]);
        
        $response->getBody()->write($html);
        
        return $response;
    }
}
```

### Príklad Latte šablóny

```latte
<!-- views/home.latte -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <link rel="stylesheet" href="{$paths->getPublicPath()}/css/style.css">
</head>
<body>
    <h1>{$title}</h1>
    
    <div class="content">
        {$content}
    </div>
    
    <footer>
        <p>Cesta k views: {$paths->getViewsPath()}</p>
    </footer>
    
    <script src="{$paths->getPublicPath()}/js/app.js"></script>
</body>
</html>
```

## Smarty

[Smarty](https://www.smarty.net/) je template engine pre PHP, ktorý oddeľuje PHP od HTML.

### Inštalácia

```bash
composer require smarty/smarty
```

### Integrácia s `slim4/root`

```php
<?php

namespace App\Views;

use Smarty;
use Slim4\Root\PathsInterface;

class SmartyRenderer
{
    private Smarty $smarty;
    private PathsInterface $paths;
    
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
        
        // Vytvorenie inštancie Smarty
        $this->smarty = new Smarty();
        
        // Nastavenie ciest
        $viewsPath = $paths->getViewsPath();
        $cachePath = $paths->getCachePath() . '/smarty/cache';
        $compilePath = $paths->getCachePath() . '/smarty/compile';
        
        // Vytvorenie adresárov, ak neexistujú
        foreach ([$cachePath, $compilePath] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        
        $this->smarty->setTemplateDir($viewsPath);
        $this->smarty->setCacheDir($cachePath);
        $this->smarty->setCompileDir($compilePath);
        
        // Pridanie globálnych dát
        $this->smarty->assign('paths', $paths);
        
        // Registrácia funkcií
        $this->registerFunctions();
    }
    
    private function registerFunctions(): void
    {
        // Pridanie funkcie pre prístup k cestám
        $paths = $this->paths;
        $this->smarty->registerPlugin('function', 'path', function ($params) use ($paths) {
            return $paths->path($params['to']);
        });
    }
    
    public function render(string $template, array $data = []): string
    {
        // Pridanie dát do Smarty
        foreach ($data as $key => $value) {
            $this->smarty->assign($key, $value);
        }
        
        // Renderovanie šablóny
        return $this->smarty->fetch($template . '.tpl');
    }
    
    public function getSmarty(): Smarty
    {
        return $this->smarty;
    }
}
```

### Registrácia v DI kontajneri

```php
<?php

use App\Views\SmartyRenderer;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim4\Root\PathsInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Registrácia SmartyRenderer do kontajnera
        SmartyRenderer::class => function (ContainerInterface $c) {
            $paths = $c->get(PathsInterface::class);
            return new SmartyRenderer($paths);
        },
    ]);
};
```

### Použitie v kontroleri

```php
<?php

namespace App\Controllers;

use App\Views\SmartyRenderer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController
{
    private SmartyRenderer $smarty;
    
    public function __construct(SmartyRenderer $smarty)
    {
        $this->smarty = $smarty;
    }
    
    public function index(Request $request, Response $response): Response
    {
        $html = $this->smarty->render('home', [
            'title' => 'Domovská stránka',
            'content' => 'Vitajte na našej stránke!',
        ]);
        
        $response->getBody()->write($html);
        
        return $response;
    }
}
```

### Príklad Smarty šablóny

```smarty
<!-- views/home.tpl -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <link rel="stylesheet" href="{$paths->getPublicPath()}/css/style.css">
</head>
<body>
    <h1>{$title}</h1>
    
    <div class="content">
        {$content}
    </div>
    
    <footer>
        <p>Cesta k views: {$paths->getViewsPath()}</p>
    </footer>
    
    <script src="{$paths->getPublicPath()}/js/app.js"></script>
</body>
</html>
```

## Záver

Balík `slim4/root` môže byť jednoducho integrovaný s rôznymi template enginami, čo vám umožňuje používať vaše obľúbené nástroje a zároveň mať konzistentný prístup k cestám vo vašom projekte. Vďaka tomu môžete ľahko pristupovať k rôznym adresárom a súborom priamo z vašich šablón.
