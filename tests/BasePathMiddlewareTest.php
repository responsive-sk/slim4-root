<?php

declare(strict_types=1);

namespace Slim4\Root\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim4\Root\BasePathMiddleware;

/**
 * BasePathMiddleware Test.
 */
class BasePathMiddlewareTest extends TestCase
{
    /**
     * Test process with CLI server.
     */
    public function testProcessWithCliServer(): void
    {
        $app = $this->createMock(App::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $serverParams = [
            'SCRIPT_NAME' => '/index.php',
        ];

        $request->method('getServerParams')->willReturn($serverParams);
        $handler->method('handle')->willReturn($response);

        $app->expects($this->once())
            ->method('setBasePath')
            ->with('');

        $middleware = new BasePathMiddleware($app, 'cli-server');
        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    /**
     * Test process with Apache server.
     */
    public function testProcessWithApacheServer(): void
    {
        $app = $this->createMock(App::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $serverParams = [
            'SCRIPT_NAME' => '/my-app/public/index.php',
            'REQUEST_URI' => '/my-app/users',
        ];

        $request->method('getServerParams')->willReturn($serverParams);
        $handler->method('handle')->willReturn($response);

        $app->expects($this->once())
            ->method('setBasePath')
            ->with('/my-app');

        $middleware = new BasePathMiddleware($app, 'apache');
        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    /**
     * Test process with root path.
     */
    public function testProcessWithRootPath(): void
    {
        $app = $this->createMock(App::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $serverParams = [
            'SCRIPT_NAME' => '/public/index.php',
            'REQUEST_URI' => '/users',
        ];

        $request->method('getServerParams')->willReturn($serverParams);
        $handler->method('handle')->willReturn($response);

        $app->expects($this->once())
            ->method('setBasePath')
            ->with('');

        $middleware = new BasePathMiddleware($app, 'apache');
        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    /**
     * Test process without REQUEST_URI.
     */
    public function testProcessWithoutRequestUri(): void
    {
        $app = $this->createMock(App::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $serverParams = [
            'SCRIPT_NAME' => '/public/index.php',
        ];

        $request->method('getServerParams')->willReturn($serverParams);
        $handler->method('handle')->willReturn($response);

        $app->expects($this->once())
            ->method('setBasePath')
            ->with('');

        $middleware = new BasePathMiddleware($app, 'apache');
        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
