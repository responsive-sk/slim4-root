# Slim4 Root - Integration with Template Engines

This document contains examples of integrating the `slim4/root` package with popular PHP template engines.

## Table of Contents

1. [Blade (Laravel)](#blade-laravel)
2. [Plates](#plates)
3. [Volt (Phalcon)](#volt-phalcon)
4. [Twig](#twig)
5. [Latte](#latte)
6. [Smarty](#smarty)

## Blade (Laravel)

[Blade](https://laravel.com/docs/blade) is a popular template engine from the Laravel framework, which you can also use in Slim 4 projects using the [jenssegers/blade](https://github.com/jenssegers/blade) package.

### Installation

```bash
composer require jenssegers/blade
```

### Integration with `slim4/root`

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
        // Create Blade instance with paths from Paths
        $viewsPath = $paths->getViewsPath();
        $cachePath = $paths->getCachePath() . '/views';
        
        // Create cache directory if it doesn't exist
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
        
        $this->blade = new Blade($viewsPath, $cachePath);
        
        // Add global data
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

### Registration in DI Container

```php
<?php

use App\Views\BladeRenderer;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim4\Root\PathsInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Register BladeRenderer in the container
        BladeRenderer::class => function (ContainerInterface $c) {
            $paths = $c->get(PathsInterface::class);
            return new BladeRenderer($paths);
        },
    ]);
};
```

### Usage in Controller

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
            'title' => 'Home Page',
            'content' => 'Welcome to our website!',
        ]);
        
        $response->getBody()->write($html);
        
        return $response;
    }
}
```

### Example Blade Template

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
        <p>Views path: {{ $paths->getViewsPath() }}</p>
    </footer>
    
    <script src="{{ $paths->getPublicPath() }}/js/app.js"></script>
</body>
</html>
```

### Creating a Custom Blade Directive

```php
<?php

// Add a custom directive
$blade->addDirective('pathto', function ($expression) {
    return "<?php echo \$paths->path($expression); ?>";
});
```

Then you can use the directive in your template:

```blade
<img src="@pathto('public/images/logo.png')" alt="Logo">
```

## Plates

[Plates](https://platesphp.com/) is a modern, native PHP template engine that is simple and has no dependencies.

### Installation

```bash
composer require league/plates
```

### Integration with `slim4/root`

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
        
        // Create Plates instance with path from Paths
        $viewsPath = $paths->getViewsPath();
        $this->plates = new Engine($viewsPath);
        
        // Add global data
        $this->plates->addData(['paths' => $paths]);
        
        // Register template directories
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

### Registration in DI Container

```php
<?php

use App\Views\PlatesRenderer;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim4\Root\PathsInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Register PlatesRenderer in the container
        PlatesRenderer::class => function (ContainerInterface $c) {
            $paths = $c->get(PathsInterface::class);
            return new PlatesRenderer($paths);
        },
    ]);
};
```

### Usage in Controller

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
            'title' => 'Home Page',
            'content' => 'Welcome to our website!',
        ]);
        
        $response->getBody()->write($html);
        
        return $response;
    }
}
```

### Example Plates Template

```php
<!-- views/home.php -->
<?php $this->layout('layouts::default', ['title' => $title]) ?>

<h1><?= $this->e($title) ?></h1>

<div class="content">
    <?= $this->e($content) ?>
</div>

<footer>
    <p>Views path: <?= $this->e($paths->getViewsPath()) ?></p>
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

### Creating a Custom Plates Function

```php
<?php

// Add a custom function
$plates->addFunction('pathTo', function ($path) use ($paths) {
    return $paths->path($path);
});
```

Then you can use the function in your template:

```php
<img src="<?= $this->pathTo('public/images/logo.png') ?>" alt="Logo">
```

## Volt (Phalcon)

[Volt](https://docs.phalcon.io/4.0/en/volt) is a fast and flexible template engine for PHP, which is part of the Phalcon framework. You can also use it standalone with the [phalcon/volt](https://github.com/phalcon/volt) package.

### Installation

```bash
composer require phalcon/volt
```

### Integration with `slim4/root`

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
        
        // Create Volt instance with paths from Paths
        $this->volt = new Compiler();
        
        // Set cache path
        $cachePath = $paths->getCachePath() . '/volt';
        
        // Create cache directory if it doesn't exist
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
        
        $this->volt->setOptions([
            'compiledPath' => $cachePath,
            'compiledSeparator' => '_',
        ]);
        
        // Register functions
        $this->registerFunctions();
    }
    
    private function registerFunctions(): void
    {
        // Add function for accessing paths
        $paths = $this->paths;
        $this->volt->addFunction('path', function ($resolvedArgs, $exprArgs) use ($paths) {
            return '$paths->path(' . $resolvedArgs . ')';
        });
        
        // Add functions for accessing standard paths
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
        // Add paths to data
        $data['paths'] = $this->paths;
        
        // Get template path
        $viewsPath = $this->paths->getViewsPath();
        $templatePath = $viewsPath . '/' . $template . '.volt';
        
        // Compile template
        $compiledTemplate = $this->volt->compile($templatePath);
        
        // Extract data for template
        extract($data);
        
        // Capture output
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

### Registration in DI Container

```php
<?php

use App\Views\VoltRenderer;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim4\Root\PathsInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Register VoltRenderer in the container
        VoltRenderer::class => function (ContainerInterface $c) {
            $paths = $c->get(PathsInterface::class);
            return new VoltRenderer($paths);
        },
    ]);
};
```

### Usage in Controller

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
            'title' => 'Home Page',
            'content' => 'Welcome to our website!',
        ]);
        
        $response->getBody()->write($html);
        
        return $response;
    }
}
```

### Example Volt Template

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
        <p>Views path: {{ getViewsPath() }}</p>
    </footer>
    
    <script src="{{ getPublicPath() }}/js/app.js"></script>
</body>
</html>
```

## Twig

[Twig](https://twig.symfony.com/) is a flexible, fast, and secure template engine for PHP.

### Installation

```bash
composer require slim/twig-view
```

### Integration with `slim4/root`

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
        // Create Twig instance with paths from Paths
        $viewsPath = $paths->getViewsPath();
        $cachePath = $paths->getCachePath() . '/twig';
        
        // Create cache directory if it doesn't exist
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
        
        $this->twig = Twig::create($viewsPath, [
            'cache' => $cachePath,
            'auto_reload' => true,
        ]);
        
        // Add global data
        $this->twig->getEnvironment()->addGlobal('paths', $paths);
        
        // Register functions
        $this->registerFunctions($paths);
    }
    
    private function registerFunctions(PathsInterface $paths): void
    {
        // Add function for accessing paths
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

### Registration in DI Container

```php
<?php

use App\Views\TwigRenderer;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim4\Root\PathsInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Register TwigRenderer in the container
        TwigRenderer::class => function (ContainerInterface $c) {
            $paths = $c->get(PathsInterface::class);
            return new TwigRenderer($paths);
        },
    ]);
};
```

### Usage in Controller

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
            'title' => 'Home Page',
            'content' => 'Welcome to our website!',
        ]);
        
        $response->getBody()->write($html);
        
        return $response;
    }
}
```

### Example Twig Template

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
        <p>Views path: {{ paths.getViewsPath() }}</p>
    </footer>
    
    <script src="{{ paths.getPublicPath() }}/js/app.js"></script>
</body>
</html>
```

## Latte

[Latte](https://latte.nette.org/) is a safe and intuitive template engine for PHP.

### Installation

```bash
composer require latte/latte
```

### Integration with `slim4/root`

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
        
        // Create Latte instance
        $this->latte = new Engine();
        
        // Set cache path
        $cachePath = $paths->getCachePath() . '/latte';
        
        // Create cache directory if it doesn't exist
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
        
        $this->latte->setTempDirectory($cachePath);
        
        // Add global data
        $this->latte->addProvider('paths', $paths);
        
        // Register functions
        $this->registerFunctions();
    }
    
    private function registerFunctions(): void
    {
        // Add function for accessing paths
        $paths = $this->paths;
        $this->latte->addFunction('path', function (string $path) use ($paths) {
            return $paths->path($path);
        });
    }
    
    public function render(string $template, array $data = []): string
    {
        // Add paths to data
        $data['paths'] = $this->paths;
        
        // Get template path
        $viewsPath = $this->paths->getViewsPath();
        $templatePath = $viewsPath . '/' . $template . '.latte';
        
        // Render template
        return $this->latte->renderToString($templatePath, $data);
    }
    
    public function getLatte(): Engine
    {
        return $this->latte;
    }
}
```

### Registration in DI Container

```php
<?php

use App\Views\LatteRenderer;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim4\Root\PathsInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Register LatteRenderer in the container
        LatteRenderer::class => function (ContainerInterface $c) {
            $paths = $c->get(PathsInterface::class);
            return new LatteRenderer($paths);
        },
    ]);
};
```

### Usage in Controller

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
            'title' => 'Home Page',
            'content' => 'Welcome to our website!',
        ]);
        
        $response->getBody()->write($html);
        
        return $response;
    }
}
```

### Example Latte Template

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
        <p>Views path: {$paths->getViewsPath()}</p>
    </footer>
    
    <script src="{$paths->getPublicPath()}/js/app.js"></script>
</body>
</html>
```

## Smarty

[Smarty](https://www.smarty.net/) is a template engine for PHP that separates PHP from HTML.

### Installation

```bash
composer require smarty/smarty
```

### Integration with `slim4/root`

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
        
        // Create Smarty instance
        $this->smarty = new Smarty();
        
        // Set paths
        $viewsPath = $paths->getViewsPath();
        $cachePath = $paths->getCachePath() . '/smarty/cache';
        $compilePath = $paths->getCachePath() . '/smarty/compile';
        
        // Create directories if they don't exist
        foreach ([$cachePath, $compilePath] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        
        $this->smarty->setTemplateDir($viewsPath);
        $this->smarty->setCacheDir($cachePath);
        $this->smarty->setCompileDir($compilePath);
        
        // Add global data
        $this->smarty->assign('paths', $paths);
        
        // Register functions
        $this->registerFunctions();
    }
    
    private function registerFunctions(): void
    {
        // Add function for accessing paths
        $paths = $this->paths;
        $this->smarty->registerPlugin('function', 'path', function ($params) use ($paths) {
            return $paths->path($params['to']);
        });
    }
    
    public function render(string $template, array $data = []): string
    {
        // Add data to Smarty
        foreach ($data as $key => $value) {
            $this->smarty->assign($key, $value);
        }
        
        // Render template
        return $this->smarty->fetch($template . '.tpl');
    }
    
    public function getSmarty(): Smarty
    {
        return $this->smarty;
    }
}
```

### Registration in DI Container

```php
<?php

use App\Views\SmartyRenderer;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim4\Root\PathsInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Register SmartyRenderer in the container
        SmartyRenderer::class => function (ContainerInterface $c) {
            $paths = $c->get(PathsInterface::class);
            return new SmartyRenderer($paths);
        },
    ]);
};
```

### Usage in Controller

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
            'title' => 'Home Page',
            'content' => 'Welcome to our website!',
        ]);
        
        $response->getBody()->write($html);
        
        return $response;
    }
}
```

### Example Smarty Template

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
        <p>Views path: {$paths->getViewsPath()}</p>
    </footer>
    
    <script src="{$paths->getPublicPath()}/js/app.js"></script>
</body>
</html>
```

## Conclusion

The `slim4/root` package can be easily integrated with various template engines, allowing you to use your favorite tools while having consistent access to paths in your project. This makes it easy to access different directories and files directly from your templates.
