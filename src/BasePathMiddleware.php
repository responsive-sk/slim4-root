<?php

declare(strict_types=1);

namespace Slim4\Root;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;

/**
 * Base path middleware for Slim 4.
 *
 * This middleware detects and sets the base path for the Slim application.
 * It's similar to Selective\BasePath\BasePathMiddleware but with explicit nullable type.
 */
class BasePathMiddleware implements MiddlewareInterface
{
    /**
     * @var App The Slim app
     */
    private App $app;

    /**
     * @var string|null The PHP_SAPI value
     */
    private ?string $phpSapi;

    /**
     * Constructor.
     *
     * @param App $app The Slim app
     * @param string|null $phpSapi The PHP_SAPI value (default: null)
     */
    public function __construct(App $app, ?string $phpSapi = null)
    {
        $this->app = $app;
        $this->phpSapi = $phpSapi ?? PHP_SAPI;
    }

    /**
     * Process an incoming server request.
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $basePath = $this->detectBasePath($request->getServerParams());
        $this->app->setBasePath($basePath);

        return $handler->handle($request);
    }

    /**
     * Detect the base path from server parameters.
     *
     * @param array<string, mixed> $serverParams The server parameters
     *
     * @return string The detected base path
     */
    private function detectBasePath(array $serverParams): string
    {
        // For PHP built-in server
        if ($this->phpSapi === 'cli-server') {
            return $this->getBasePathByScriptName($serverParams);
        }

        // For Apache, Nginx, etc.
        return $this->getBasePathByRequestUri($serverParams);
    }

    /**
     * Get base path for built-in server.
     *
     * @param array<string, mixed> $serverParams The server parameters
     *
     * @return string The base path
     */
    private function getBasePathByScriptName(array $serverParams): string
    {
        $scriptName = (string)($serverParams['SCRIPT_NAME'] ?? '');
        $basePath = str_replace('\\', '/', dirname($scriptName));

        if (strlen($basePath) > 1) {
            return $basePath;
        }

        return '';
    }

    /**
     * Get base path for Apache, Nginx, etc.
     *
     * @param array<string, mixed> $serverParams The server parameters
     *
     * @return string The base path
     */
    private function getBasePathByRequestUri(array $serverParams): string
    {
        if (!isset($serverParams['REQUEST_URI'])) {
            return '';
        }

        $scriptName = $serverParams['SCRIPT_NAME'] ?? '';
        $requestUri = (string)$serverParams['REQUEST_URI'];

        $basePath = (string)parse_url($requestUri, PHP_URL_PATH);
        $scriptDir = str_replace('\\', '/', dirname((string)$scriptName, 2));

        if ($scriptDir === '/') {
            return '';
        }

        $length = strlen($scriptDir);
        if ($length > 0) {
            $basePath = substr($basePath, 0, $length);
        }

        if (strlen($basePath) > 1) {
            return $basePath;
        }

        return '';
    }
}