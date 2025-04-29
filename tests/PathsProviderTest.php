<?php

declare(strict_types=1);

namespace Slim4\Root\Tests;

use DI\Container;
use PHPUnit\Framework\TestCase;
use Slim4\Root\Paths;
use Slim4\Root\PathsInterface;
use Slim4\Root\PathsMiddleware;
use Slim4\Root\PathsProvider;

/**
 * Paths provider test.
 */
class PathsProviderTest extends TestCase
{
    /**
     * Test register.
     *
     * @return void
     */
    public function testRegister(): void
    {
        $container = new Container();
        $rootPath = '/var/www/app';
        $customPaths = [
            'config' => '/var/www/app/custom/config',
        ];

        PathsProvider::register($container, $rootPath, $customPaths);

        $paths = $container->get(PathsInterface::class);
        $this->assertInstanceOf(Paths::class, $paths);
        $this->assertSame($rootPath, $paths->getRootPath());
        $this->assertSame('/var/www/app/custom/config', $paths->getConfigPath());

        $middleware = $container->get(PathsMiddleware::class);
        $this->assertInstanceOf(PathsMiddleware::class, $middleware);
    }
}
