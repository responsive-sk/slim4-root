<?php

declare(strict_types=1);

namespace Slim4\Root;

use Psr\Container\ContainerInterface;

/**
 * Paths provider.
 */
class PathsProvider
{
    /**
     * Register paths services with the container.
     *
     * @param ContainerInterface    $container     The container
     * @param string                $rootPath      The root path
     * @param array<string, string> $customPaths   Custom paths configuration
     * @param bool                  $autoDiscover  Whether to auto-discover paths
     * @param bool                  $validatePaths Whether to validate paths
     *
     * @return void
     */
    public static function register(
        ContainerInterface $container,
        string $rootPath,
        array $customPaths = [],
        bool $autoDiscover = true,
        bool $validatePaths = false
    ): void {
        if (method_exists($container, 'set')) {
            // PHP-DI
            $container->set(
                PathsInterface::class,
                function () use (
                    $rootPath,
                    $customPaths,
                    $autoDiscover,
                    $validatePaths
                ) {
                    return new Paths($rootPath, $customPaths, $autoDiscover, $validatePaths);
                }
            );

            $container->set(
                PathsMiddleware::class,
                function (ContainerInterface $container) {
                    /**
                * @var PathsInterface $paths
                */
                    $paths = $container->get(PathsInterface::class);
                    return new PathsMiddleware($paths);
                }
            );

            $container->set(
                PathsDiscoverer::class,
                function () {
                    return new PathsDiscoverer();
                }
            );

            $container->set(
                PathsValidator::class,
                function () {
                    return new PathsValidator();
                }
            );

            $container->set(
                PathsNormalizer::class,
                function () {
                    return new PathsNormalizer();
                }
            );
        } elseif (method_exists($container, 'setService')) {
            // Slim Container
            $container->setService(
                PathsInterface::class,
                function () use (
                    $rootPath,
                    $customPaths,
                    $autoDiscover,
                    $validatePaths
                ) {
                    return new Paths($rootPath, $customPaths, $autoDiscover, $validatePaths);
                }
            );

            $container->setService(
                PathsMiddleware::class,
                function (ContainerInterface $container) {
                    /**
                * @var PathsInterface $paths
                */
                    $paths = $container->get(PathsInterface::class);
                    return new PathsMiddleware($paths);
                }
            );

            $container->setService(
                PathsDiscoverer::class,
                function () {
                    return new PathsDiscoverer();
                }
            );

            $container->setService(
                PathsValidator::class,
                function () {
                    return new PathsValidator();
                }
            );

            $container->setService(
                PathsNormalizer::class,
                function () {
                    return new PathsNormalizer();
                }
            );
        } else {
            throw new \RuntimeException('Unsupported container implementation');
        }
    }
}
