<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Path\Discovery;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Path\PathBasedLinter;
use PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub\ItemInterface;
use PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub\PlannedItem;
use PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub\TypedPropertiesMessage;
use PHPUnit\Framework\TestCase;

use function dirname;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Linter\Path\Discovery\ClassNameDiscovery
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
            PathBasedLinter::class,
        ], $classNames);
    }
}
