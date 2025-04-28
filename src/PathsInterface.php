<?php

declare(strict_types=1);

namespace Slim4\Root;

/**
 * Paths interface.
 */
interface PathsInterface
{
    /**
     * Get the root path of the project.
     *
     * @return string The root path
     */
    public function getRootPath(): string;

    /**
     * Get the config path.
     *
     * @return string The config path
     */
    public function getConfigPath(): string;

    /**
     * Get the resources path.
     *
     * @return string The resources path
     */
    public function getResourcesPath(): string;

    /**
     * Get the views path.
     *
     * @return string The views path
     */
    public function getViewsPath(): string;

    /**
     * Get the assets path.
     *
     * @return string The assets path
     */
    public function getAssetsPath(): string;

    /**
     * Get the cache path.
     *
     * @return string The cache path
     */
    public function getCachePath(): string;

    /**
     * Get the logs path.
     *
     * @return string The logs path
     */
    public function getLogsPath(): string;

    /**
     * Get the public path.
     *
     * @return string The public path
     */
    public function getPublicPath(): string;

    /**
     * Get the database path.
     *
     * @return string The database path
     */
    public function getDatabasePath(): string;

    /**
     * Get the migrations path.
     *
     * @return string The migrations path
     */
    public function getMigrationsPath(): string;

    /**
     * Get the storage path.
     *
     * @return string The storage path
     */
    public function getStoragePath(): string;

    /**
     * Get the tests path.
     *
     * @return string The tests path
     */
    public function getTestsPath(): string;

    /**
     * Get a path relative to the root path.
     *
     * @param string $path The relative path
     *
     * @return string The absolute path
     */
    public function path(string $path): string;

    /**
     * Get all paths.
     *
     * @return array<string, string> The paths
     */
    public function getPaths(): array;

    /**
     * Get the build path.
     *
     * @param string $buildDirectory Build directory name (default: 'build')
     * @return string The build path
     */
    public function getBuildPath(string $buildDirectory = 'build'): string;

    /**
     * Get the build assets path.
     *
     * Note: This method ignores the $buildDirectory parameter and always returns the public/assets path.
     *
     * @param string $buildDirectory Build directory name (ignored, kept for backward compatibility)
     * @return string The assets path (public/assets)
     */
    public function getBuildAssetsPath(string $buildDirectory = 'build'): string;

    /**
     * Get the Vite manifest path.
     *
     * Note: This method ignores the $buildDirectory parameter and always looks in the public/assets directory.
     *
     * @param string $buildDirectory Build directory name (ignored, kept for backward compatibility)
     * @return string The Vite manifest path (in public/assets directory)
     */
    public function getViteManifestPath(string $buildDirectory = 'build'): string;
}
