<?php

declare(strict_types=1);

namespace Slim4\Root\Tests;

use PHPUnit\Framework\TestCase;
use Slim4\Root\Paths;

/**
 * Paths test.
 */
class PathsTest extends TestCase
{
    /**
     * Test constructor.
     *
     * @return void
     */
    public function testConstructor(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath, [], false, false);

        $this->assertSame($rootPath, $paths->getRootPath());
    }

    /**
     * Test constructor with trailing slash.
     *
     * @return void
     */
    public function testConstructorWithTrailingSlash(): void
    {
        $rootPath = '/var/www/app/';
        $paths = new Paths($rootPath, [], false, false);

        $this->assertSame('/var/www/app', $paths->getRootPath());
    }

    /**
     * Test constructor with custom paths.
     *
     * @return void
     */
    public function testConstructorWithCustomPaths(): void
    {
        $rootPath = '/var/www/app';
        $customPaths = [
            'config' => '/var/www/app/custom/config',
            'views' => '/var/www/app/custom/views',
        ];
        $paths = new Paths($rootPath, $customPaths, false, false);

        $this->assertSame($rootPath, $paths->getRootPath());
        $this->assertSame('/var/www/app/custom/config', $paths->getConfigPath());
        $this->assertSame('/var/www/app/custom/views', $paths->getViewsPath());
    }

    /**
     * Test getConfigPath.
     *
     * @return void
     */
    public function testGetConfigPath(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath, [], false, false);

        $this->assertSame('/var/www/app/config', $paths->getConfigPath());
    }

    /**
     * Test getResourcesPath.
     *
     * @return void
     */
    public function testGetResourcesPath(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath);

        $this->assertSame('/var/www/app/resources', $paths->getResourcesPath());
    }

    /**
     * Test getViewsPath.
     *
     * @return void
     */
    public function testGetViewsPath(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath);

        $this->assertSame('/var/www/app/resources/views', $paths->getViewsPath());
    }

    /**
     * Test getAssetsPath.
     *
     * @return void
     */
    public function testGetAssetsPath(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath);

        $this->assertSame('/var/www/app/resources/assets', $paths->getAssetsPath());
    }

    /**
     * Test getCachePath.
     *
     * @return void
     */
    public function testGetCachePath(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath);

        $this->assertSame('/var/www/app/var/cache', $paths->getCachePath());
    }

    /**
     * Test getLogsPath.
     *
     * @return void
     */
    public function testGetLogsPath(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath);

        $this->assertSame('/var/www/app/var/logs', $paths->getLogsPath());
    }

    /**
     * Test getPublicPath.
     *
     * @return void
     */
    public function testGetPublicPath(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath);

        $this->assertSame('/var/www/app/public', $paths->getPublicPath());
    }

    /**
     * Test getDatabasePath.
     *
     * @return void
     */
    public function testGetDatabasePath(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath);

        $this->assertSame('/var/www/app/database', $paths->getDatabasePath());
    }

    /**
     * Test getMigrationsPath.
     *
     * @return void
     */
    public function testGetMigrationsPath(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath);

        $this->assertSame('/var/www/app/database/migrations', $paths->getMigrationsPath());
    }

    /**
     * Test getStoragePath.
     *
     * @return void
     */
    public function testGetStoragePath(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath);

        $this->assertSame('/var/www/app/storage', $paths->getStoragePath());
    }

    /**
     * Test getTestsPath.
     *
     * @return void
     */
    public function testGetTestsPath(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath);

        $this->assertSame('/var/www/app/tests', $paths->getTestsPath());
    }

    /**
     * Test path.
     *
     * @return void
     */
    public function testPath(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath, [], false, false);

        $this->assertSame('/var/www/app/config/app.php', $paths->path('config/app.php'));
        $this->assertSame('/var/www/app/config/app.php', $paths->path('/config/app.php'));
    }

    /**
     * Test getPaths.
     *
     * @return void
     */
    public function testGetPaths(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath, [], false, false);

        $allPaths = $paths->getPaths();

        // Ensure the result is an array with expected keys
        $this->assertArrayHasKey('root', $allPaths);
        $this->assertArrayHasKey('config', $allPaths);
        $this->assertArrayHasKey('resources', $allPaths);
        $this->assertArrayHasKey('views', $allPaths);
        $this->assertArrayHasKey('assets', $allPaths);
        $this->assertArrayHasKey('cache', $allPaths);
        $this->assertArrayHasKey('logs', $allPaths);
        $this->assertArrayHasKey('public', $allPaths);
        $this->assertArrayHasKey('database', $allPaths);
        $this->assertArrayHasKey('migrations', $allPaths);
        $this->assertArrayHasKey('storage', $allPaths);
        $this->assertArrayHasKey('tests', $allPaths);

        $this->assertSame($rootPath, $allPaths['root']);
        $this->assertSame($rootPath . '/config', $allPaths['config']);
    }

    /**
     * Test auto-discovery.
     *
     * @return void
     */
    public function testAutoDiscovery(): void
    {
        $rootPath = sys_get_temp_dir() . '/slim4-path-test-' . uniqid();
        mkdir($rootPath . '/config', 0777, true);
        mkdir($rootPath . '/templates', 0777, true);
        mkdir($rootPath . '/public', 0777, true);

        $paths = new Paths($rootPath, [], true, false);

        $this->assertSame($rootPath . '/config', $paths->getConfigPath());
        $this->assertSame($rootPath . '/templates', $paths->getViewsPath());
        $this->assertSame($rootPath . '/public', $paths->getPublicPath());

        // Clean up
        $this->removeDirectory($rootPath);
    }

    /**
     * Test getBuildPath.
     *
     * @return void
     */
    public function testGetBuildPath(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath);

        $this->assertSame('/var/www/app/public/build', $paths->getBuildPath());
        $this->assertSame('/var/www/app/public/dist', $paths->getBuildPath('dist'));
    }

    /**
     * Test getBuildAssetsPath.
     *
     * @return void
     */
    public function testGetBuildAssetsPath(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath);

        // The method should always return public/assets regardless of the buildDirectory parameter
        $this->assertSame('/var/www/app/public/assets', $paths->getBuildAssetsPath());
        $this->assertSame('/var/www/app/public/assets', $paths->getBuildAssetsPath('dist'));
    }

    /**
     * Test getViteManifestPath.
     *
     * @return void
     */
    public function testGetViteManifestPath(): void
    {
        $rootPath = sys_get_temp_dir() . '/slim4-path-test-' . uniqid();
        mkdir($rootPath . '/public/assets/.vite', 0777, true);
        file_put_contents($rootPath . '/public/assets/.vite/manifest.json', '{}');

        $paths = new Paths($rootPath);

        $this->assertSame($rootPath . '/public/assets/.vite/manifest.json', $paths->getViteManifestPath());

        // Test with manifest.json directly in assets directory
        unlink($rootPath . '/public/assets/.vite/manifest.json');
        rmdir($rootPath . '/public/assets/.vite');
        file_put_contents($rootPath . '/public/assets/manifest.json', '{}');

        $this->assertSame($rootPath . '/public/assets/manifest.json', $paths->getViteManifestPath());

        // The buildDirectory parameter should be ignored
        $this->assertSame($rootPath . '/public/assets/manifest.json', $paths->getViteManifestPath('dist'));

        // Clean up
        $this->removeDirectory($rootPath);
    }

    /**
     * Test getViteManifestPath with non-existent manifest.
     *
     * @return void
     */
    public function testGetViteManifestPathWithNonExistentManifest(): void
    {
        $rootPath = '/var/www/app';
        $paths = new Paths($rootPath);

        $this->assertSame('/var/www/app/public/assets/.vite/manifest.json', $paths->getViteManifestPath());
        // The buildDirectory parameter should be ignored
        $this->assertSame('/var/www/app/public/assets/.vite/manifest.json', $paths->getViteManifestPath('dist'));
    }

    /**
     * Remove directory recursively.
     *
     * @param string $path The path
     *
     * @return void
     */
    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $path . '/' . $file;
            if (is_dir($filePath)) {
                $this->removeDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }

        rmdir($path);
    }
}
