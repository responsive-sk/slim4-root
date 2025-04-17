<?php

declare(strict_types=1);

namespace Slim4\Root\Tests;

use PHPUnit\Framework\TestCase;
use Slim4\Root\Exception\InvalidPathException;
use Slim4\Root\PathsValidator;

/**
 * Paths validator test.
 */
class PathsValidatorTest extends TestCase
{
    /**
     * Test validate.
     *
     * @return void
     */
    public function testValidate(): void
    {
        $rootPath = sys_get_temp_dir() . '/slim4-path-test-' . uniqid();
        mkdir($rootPath . '/config', 0777, true);
        mkdir($rootPath . '/resources/views', 0777, true);

        $validator = new PathsValidator();
        $validator->validate(
            [
            'config' => $rootPath . '/config',
            'views' => $rootPath . '/resources/views',
            ],
            true
        );

        // No exception means validation passed
        // This assertion is just to mark the test as passed
        $this->addToAssertionCount(1);

        // Clean up
        $this->removeDirectory($rootPath);
    }

    /**
     * Test validate with non-existent paths.
     *
     * @return void
     */
    public function testValidateWithNonExistentPaths(): void
    {
        $rootPath = sys_get_temp_dir() . '/slim4-path-test-' . uniqid();
        mkdir($rootPath, 0777, true);

        $validator = new PathsValidator();

        // Non-strict validation should not throw an exception
        $validator->validate(
            [
            'config' => $rootPath . '/config',
            'views' => $rootPath . '/resources/views',
            ],
            false
        );

        // No exception means validation passed
        // This assertion is just to mark the test as passed
        $this->addToAssertionCount(1);

        // Strict validation should throw an exception
        $this->expectException(InvalidPathException::class);
        $validator->validate(
            [
            'config' => $rootPath . '/config',
            'views' => $rootPath . '/resources/views',
            ],
            true
        );

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
