<?php

declare(strict_types=1);

namespace Slim4\Root;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Paths middleware.
 */
class PathsMiddleware implements MiddlewareInterface
{
    /**
     * @var PathsInterface The paths
     */
    private PathsInterface $paths;

    /**
     * Constructor.
     *
     * @param PathsInterface $paths The paths
     */
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
    }

    /**
     * Process an incoming server request.
     *
     * @param ServerRequestInterface  $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Add paths to the request attributes
        $request = $request->withAttribute('paths', $this->paths);

        return $handler->handle($request);
    }
}
