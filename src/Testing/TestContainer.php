<?php

declare(strict_types=1);

namespace Slim4\Root\Testing;

/**
 * Test container for sharing objects between tests.
 * 
 * This class provides a simple way to share objects between tests without using globals.
 */
class TestContainer
{
    /**
     * @var array<string, mixed> The container items
     */
    private static array $items = [];

    /**
     * Set an item in the container.
     *
     * @param string $key The key
     * @param mixed $value The value
     * 
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        self::$items[$key] = $value;
    }

    /**
     * Get an item from the container.
     *
     * @param string $key The key
     * @param mixed $default The default value if the key doesn't exist
     * 
     * @return mixed The value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$items[$key] ?? $default;
    }

    /**
     * Check if an item exists in the container.
     *
     * @param string $key The key
     * 
     * @return bool Whether the key exists
     */
    public static function has(string $key): bool
    {
        return isset(self::$items[$key]);
    }

    /**
     * Remove an item from the container.
     *
     * @param string $key The key
     * 
     * @return void
     */
    public static function remove(string $key): void
    {
        unset(self::$items[$key]);
    }

    /**
     * Clear all items from the container.
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$items = [];
    }
}
