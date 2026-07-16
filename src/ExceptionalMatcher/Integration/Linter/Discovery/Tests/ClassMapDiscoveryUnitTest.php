<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Discovery\Tests;

use PhPhD\ExceptionalMatcher\Integration\Linter\Discovery\ClassMapDiscovery;
use PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub\ItemInterface;
use PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub\PlannedItem;
use PhPhD\ExceptionalMatcher\Rule\Object\Tests\Stub\TypedPropertiesMessage;
use PHPUnit\Framework\TestCase;

use function dirname;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionalMatcher\Integration\Linter\Discovery\ClassMapDiscovery
 */
final class ClassMapDiscoveryUnitTest extends TestCase
{
    public function testDiscoversInstantiableClasses(): void
    {
        $discovery = new ClassMapDiscovery();

        $stubDirectory = dirname(__DIR__, 4).'/Rule/Object/Tests/Stub';

        $classNames = [...$discovery->discover([$stubDirectory])];

        self::assertContains(TypedPropertiesMessage::class, $classNames);
        self::assertContains(PlannedItem::class, $classNames);
        self::assertNotContains(ItemInterface::class, $classNames);
    }
}
