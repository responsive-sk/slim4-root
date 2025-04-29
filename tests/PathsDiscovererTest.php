<?php

declare(strict_types=1);

namespace Slim4\Root\Tests;

use PHPUnit\Framework\TestCase;
use Slim4\Root\PathsDiscoverer;

/**
 * Paths discoverer test.
 */
class PathsDiscovererTest extends TestCase
{
    /**
     * Test discover.
     *
     * @return void
     */
    public function testDiscover(): void
    {
        $rootPath = sys_get_temp_dir() . '/slim4-path-test-' . uniqid();
        mkdir($rootPath . '/config', 0777, true);
        mkdir($rootPath . '/resources/views', 0777, true);
        mkdir($rootPath . '/public', 0777, true);

        $discoverer = new PathsDiscoverer();
        $discoveredPaths = $discoverer->discover($rootPath);

        // Ensure the result is an array with expected keys
        $this->assertArrayHasKey('config', $discoveredPaths);
        $this->assertArrayHasKey('resources', $discoveredPaths);
        $this->assertArrayHasKey('views', $discoveredPaths);
        $this->assertArrayHasKey('public', $discoveredPaths);

        $this->assertSame($rootPath . '/config', $discoveredPaths['config']);
        $this->assertSame($rootPath . '/resources', $discoveredPaths['resources']);
        $this->assertSame($rootPath . '/resources/views', $discoveredPaths['views']);
        $this->assertSame($rootPath . '/public', $discoveredPaths['public']);

        // Clean up
        $this->removeDirectory($rootPath);
    }

    /**
     * Test discover with non-existent paths.
     *
     * @return void
     */
    public function testDiscoverWithNonExistentPaths(): void
    {
        $rootPath = sys_get_temp_dir() . '/slim4-path-test-' . uniqid();
        mkdir($rootPath, 0777, true);

        $discoverer = new PathsDiscoverer();
        $discoveredPaths = $discoverer->discover($rootPath);

        // Ensure the result is an empty array
        $this->assertEmpty($discoveredPaths);

        // Clean up
        rmdir($rootPath);
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
