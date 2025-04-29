<?php

declare(strict_types=1);

namespace Slim4\Root\Tests;

use PHPUnit\Framework\TestCase;
use Slim4\Root\PathsNormalizer;

/**
 * Paths normalizer test.
 */
class PathsNormalizerTest extends TestCase
{
    /**
     * Test normalize.
     *
     * @return void
     */
    public function testNormalize(): void
    {
        $normalizer = new PathsNormalizer();

        $this->assertSame('/var/www/app', $normalizer->normalize('/var/www/app'));
        $this->assertSame('/var/www/app', $normalizer->normalize('/var/www/app/'));
        $this->assertSame('C:/path/to/project', $normalizer->normalize('C:\\path\\to\\project'));
        $this->assertSame('C:/path/to/project', $normalizer->normalize('C:\\path\\to\\project\\'));
        $this->assertSame('C:/path/to/project', $normalizer->normalize('C:/path/to/project/'));
    }
}
