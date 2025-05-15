<?php

declare(strict_types=1);

namespace Slim4\Root\Integration;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Slim4\Root\PathsInterface;

/**
 * Factory for creating Monolog loggers with integration with Slim4 Root paths.
 */
class MonologFactory
{
    /**
     * @var PathsInterface The paths instance
     */
    private PathsInterface $paths;

    /**
     * @var array<string, mixed> Default configuration
     */
    private array $defaultConfig = [
        'name' => 'app',
        'path' => 'logs/app.log',
        'level' => Level::Debug,
        'rotating' => true,
        'max_files' => 7,
        'bubble' => true,
        'permission' => null,
        'locking' => false,
        'file_permission' => null,
        'use_stderr' => false,
    ];

    /**
     * Create a new MonologFactory instance.
     *
     * @param PathsInterface $paths The paths instance
     */
    public function __construct(PathsInterface $paths)
    {
        $this->paths = $paths;
    }

    /**
     * Create a new Monolog logger instance.
     *
     * @param array<string, mixed> $config Configuration options
     * @return LoggerInterface The logger instance
     */
    public function createLogger(array $config = []): LoggerInterface
    {
        $config = array_merge($this->defaultConfig, $config);
        
        $logger = new Logger($config['name']);
        $logger->pushHandler($this->createHandler($config));
        
        return $logger;
    }

    /**
     * Create a handler based on configuration.
     *
     * @param array<string, mixed> $config Configuration options
     * @return HandlerInterface The handler instance
     */
    private function createHandler(array $config): HandlerInterface
    {
        $logPath = $this->getLogPath($config['path']);
        
        if ($config['rotating']) {
            return new RotatingFileHandler(
                $logPath,
                $config['max_files'],
                $config['level'],
                $config['bubble'],
                $config['permission'],
                $config['locking']
            );
        }
        
        return new StreamHandler(
            $config['use_stderr'] ? 'php://stderr' : $logPath,
            $config['level'],
            $config['bubble'],
            $config['permission'],
            $config['locking']
        );
    }

    /**
     * Get the full path to the log file.
     *
     * @param string $path The relative path to the log file
     * @return string The full path to the log file
     */
    private function getLogPath(string $path): string
    {
        // If the path is absolute, return it as is
        if (strpos($path, '/') === 0 || (PHP_OS_FAMILY === 'Windows' && strpos($path, ':\\') !== false)) {
            return $path;
        }
        
        // If the path starts with logs/, use the logs path
        if (strpos($path, 'logs/') === 0) {
            return $this->paths->getLogsPath() . '/' . substr($path, 5);
        }
        
        // Otherwise, use the path relative to the logs path
        return $this->paths->getLogsPath() . '/' . $path;
    }

    /**
     * Create a new Monolog logger instance with a console handler.
     *
     * @param string $name The name of the logger
     * @param Level $level The minimum logging level
     * @return LoggerInterface The logger instance
     */
    public function createConsoleLogger(string $name = 'console', Level $level = Level::Debug): LoggerInterface
    {
        $logger = new Logger($name);
        $logger->pushHandler(new StreamHandler('php://stdout', $level));
        
        return $logger;
    }

    /**
     * Create a new Monolog logger instance with a file handler.
     *
     * @param string $name The name of the logger
     * @param string $path The path to the log file (relative to logs path)
     * @param Level $level The minimum logging level
     * @return LoggerInterface The logger instance
     */
    public function createFileLogger(string $name, string $path, Level $level = Level::Debug): LoggerInterface
    {
        return $this->createLogger([
            'name' => $name,
            'path' => $path,
            'level' => $level,
            'rotating' => false,
        ]);
    }

    /**
     * Create a new Monolog logger instance with a rotating file handler.
     *
     * @param string $name The name of the logger
     * @param string $path The path to the log file (relative to logs path)
     * @param int $maxFiles The maximum number of files to keep
     * @param Level $level The minimum logging level
     * @return LoggerInterface The logger instance
     */
    public function createRotatingLogger(string $name, string $path, int $maxFiles = 7, Level $level = Level::Debug): LoggerInterface
    {
        return $this->createLogger([
            'name' => $name,
            'path' => $path,
            'level' => $level,
            'rotating' => true,
            'max_files' => $maxFiles,
        ]);
    }
}
