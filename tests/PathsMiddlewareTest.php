<?php

declare(strict_types=1);

namespace Slim4\Root\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim4\Root\Paths;
use Slim4\Root\PathsInterface;
use Slim4\Root\PathsMiddleware;

/**
 * Paths middleware test.
 */
class PathsMiddlewareTest extends TestCase
{
    /**
     * Test process.
     *
     * @return void
     */
    public function testProcess(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath);

        $middleware = new PathsMiddleware($paths);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('paths', $this->isInstanceOf(PathsInterface::class))
            ->willReturnSelf();

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
