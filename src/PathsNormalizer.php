<?php

declare(strict_types=1);

namespace Slim4\Root;

/**
 * Paths normalizer.
 */
class PathsNormalizer
{
    /**
     * Normalize path.
     *
     * @param string $path The path
     *
     * @return string The normalized path
     */
    public function normalize(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }
}
