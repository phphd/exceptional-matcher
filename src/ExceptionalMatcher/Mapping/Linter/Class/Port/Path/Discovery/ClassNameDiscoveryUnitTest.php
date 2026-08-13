<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Port\Path\Discovery;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Port\Path\ClassNamePathBasedLinter;
use PHPUnit\Framework\TestCase;

use function dirname;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Port\Path\Discovery\ClassNameDiscovery
 */
final class ClassNameDiscoveryUnitTest extends TestCase
{
    public function testDiscoversInstantiableClasses(): void
    {
        $discovery = new ClassNameDiscovery();

        $directory = dirname(__DIR__);

        $classNames = $discovery->discover([$directory]);

        self::assertSame([
            ClassNameDiscovery::class,
            self::class,
            ClassNamePathBasedLinter::class,
        ], $classNames);
    }
}
