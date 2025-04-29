<?php

declare(strict_types=1);

namespace Slim4\Root;

/**
 * Paths discoverer.
 */
class PathsDiscoverer
{
    /**
     * @var array<string, array<string>> Common directory patterns
     */
    private array $commonPatterns = [
        'config' => ['config', 'app/config', 'etc'],
        'resources' => ['resources', 'app/resources', 'res'],
        'views' => ['resources/views', 'templates', 'views', 'app/views'],
        'assets' => ['resources/assets', 'assets', 'public/assets'],
        'cache' => ['var/cache', 'cache', 'tmp/cache', 'storage/cache'],
        'logs' => ['var/logs', 'logs', 'log', 'storage/logs'],
        'public' => ['public', 'web', 'www', 'htdocs'],
        'database' => ['database', 'db', 'storage/database'],
        'migrations' => ['database/migrations', 'migrations', 'db/migrations'],
        'storage' => ['storage', 'var', 'data'],
        'tests' => ['tests', 'test'],
    ];

    /**
     * Discover paths.
     *
     * @param string $rootPath The root path
     *
     * @return array<string, string> The discovered paths
     */
    public function discover(string $rootPath): array
    {
        $found = [];
        foreach ($this->commonPatterns as $type => $locations) {
            $path = $this->findFirstValidPath($rootPath, $locations);
            if ($path !== null) {
                $found[$type] = $path;
            }
        }
        return $found;
    }

    /**
     * Find the first valid path.
     *
     * @param string        $root       The root path
     * @param array<string> $candidates The candidate paths
     *
     * @return string|null The valid path or null
     */
    private function findFirstValidPath(string $root, array $candidates): ?string
    {
        foreach ($candidates as $path) {
            $fullPath = $root . '/' . $path;
            if (is_dir($fullPath)) {
                return $fullPath;
            }
        }
        return null;
    }
}
