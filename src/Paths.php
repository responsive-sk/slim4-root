<?php

declare(strict_types=1);

namespace Slim4\Root;

use Slim4\Root\Exception\InvalidPathException;

/**
 * Paths implementation.
 */
class Paths implements PathsInterface
{
    /**
     * @var string The root path
     */
    private string $rootPath;

    /**
     * @var array<string, string> Paths
     */
    private array $paths;

    /**
     * Constructor.
     *
     * @param string                $rootPath      The root path of the project
     * @param array<string, string> $customPaths   Custom paths configuration
     * @param bool                  $autoDiscover  Whether to auto-discover paths
     * @param bool                  $validatePaths Whether to validate paths
     *
     * @throws InvalidPathException If a path is invalid and validation is enabled
     */
    public function __construct(
        string $rootPath,
        array $customPaths = [],
        bool $autoDiscover = true,
        bool $validatePaths = false
    ) {
        $normalizer = new PathsNormalizer();
        $this->rootPath = $normalizer->normalize($rootPath);

        // Default paths
        $defaultPaths = [
            'root' => $this->rootPath,
            'config' => $this->rootPath . '/config',
            'resources' => $this->rootPath . '/resources',
            'views' => $this->rootPath . '/resources/views',
            'assets' => $this->rootPath . '/resources/assets',
            'cache' => $this->rootPath . '/var/cache',
            'logs' => $this->rootPath . '/var/logs',
            'public' => $this->rootPath . '/public',
            'database' => $this->rootPath . '/database',
            'migrations' => $this->rootPath . '/database/migrations',
            'storage' => $this->rootPath . '/storage',
            'tests' => $this->rootPath . '/tests',
        ];

        // Auto-discover paths
        $discoveredPaths = [];
        if ($autoDiscover) {
            $discoverer = new PathsDiscoverer();
            $discoveredPaths = $discoverer->discover($this->rootPath);
        }

        // Merge paths (custom paths have highest priority)
        $this->paths = array_merge($defaultPaths, $discoveredPaths, $customPaths);

        // Normalize all paths
        foreach ($this->paths as $key => $path) {
            $this->paths[$key] = $normalizer->normalize($path);
        }

        // Validate paths
        if ($validatePaths) {
            $validator = new PathsValidator();
            $validator->validate($this->paths, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRootPath(): string
    {
        return $this->paths['root'];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPath(): string
    {
        return $this->paths['config'];
    }

    /**
     * {@inheritdoc}
     */
    public function getResourcesPath(): string
    {
        return $this->paths['resources'];
    }

    /**
     * {@inheritdoc}
     */
    public function getViewsPath(): string
    {
        return $this->paths['views'];
    }

    /**
     * {@inheritdoc}
     */
    public function getAssetsPath(): string
    {
        return $this->paths['assets'];
    }

    /**
     * {@inheritdoc}
     */
    public function getCachePath(): string
    {
        return $this->paths['cache'];
    }

    /**
     * {@inheritdoc}
     */
    public function getLogsPath(): string
    {
        return $this->paths['logs'];
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicPath(): string
    {
        return $this->paths['public'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePath(): string
    {
        return $this->paths['database'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationsPath(): string
    {
        return $this->paths['migrations'];
    }

    /**
     * {@inheritdoc}
     */
    public function getStoragePath(): string
    {
        return $this->paths['storage'];
    }

    /**
     * {@inheritdoc}
     */
    public function getTestsPath(): string
    {
        return $this->paths['tests'];
    }

    /**
     * {@inheritdoc}
     */
    public function path(string $path): string
    {
        $normalizer = new PathsNormalizer();
        $normalizedPath = $normalizer->normalize($path);

        return $this->paths['root'] . '/' . ltrim($normalizedPath, '/');
    }

    /**
     * Get all paths.
     *
     * @return array<string, string> The paths
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * {@inheritdoc}
     */
    public function getBuildPath(string $buildDirectory = 'build'): string
    {
        return $this->getPublicPath() . '/' . $buildDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function getBuildAssetsPath(string $buildDirectory = 'build'): string
    {
        return $this->getPublicPath() . '/assets';
    }

    /**
     * {@inheritdoc}
     */
    public function getViteManifestPath(string $buildDirectory = 'build'): string
    {
        $possiblePaths = [
            $this->getPublicPath() . '/assets/manifest.json',
            $this->getPublicPath() . '/assets/.vite/manifest.json',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return $this->getPublicPath() . '/assets/.vite/manifest.json';
    }
}
