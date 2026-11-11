<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter\Class\Path\Discovery;

use PhPhD\ExceptionMapper\Mapping\Linter\Class\Path\ClassPathMappingLinter;
use PHPUnit\Framework\TestCase;

use function dirname;
use function rsort;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionMapper\Mapping\Linter\Class\Path\Discovery\ClassNameDiscovery
 */
final class ClassNameDiscoveryUnitTest extends TestCase
{
    public function testDiscoversInstantiableClasses(): void
    {
        $discovery = new ClassNameDiscovery();

        $directory = dirname(__DIR__);

        $classNames = $discovery->discover([$directory]);
        rsort($classNames);

        self::assertSame([
            self::class,
            ClassNameDiscovery::class,
            ClassPathMappingLinter::class,
        ], $classNames);
    }
}
