<?php

declare(strict_types=1);

namespace Slim4\Root;

use Slim4\Root\Exception\InvalidPathException;

/**
 * Paths validator.
 */
class PathsValidator
{
    /**
     * Validate paths.
     *
     * @param array<string, string> $paths  The paths
     * @param bool                  $strict Whether to validate strictly (throw exception if path doesn't exist)
     *
     * @throws InvalidPathException If a path is invalid
     *
     * @return void
     */
    public function validate(array $paths, bool $strict = false): void
    {
        foreach ($paths as $type => $path) {
            if ($strict && !is_dir($path)) {
                throw new InvalidPathException(
                    sprintf('Configured path for "%s" is not a valid directory: %s', $type, $path)
                );
            }
        }
    }
}
