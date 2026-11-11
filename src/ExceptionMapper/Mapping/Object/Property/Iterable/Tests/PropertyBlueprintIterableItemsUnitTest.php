<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Iterable\Tests;

use ArrayObject;
use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\ExceptionMapper;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\ExceptionMatchList;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Iterable\Tests\Stub\RootObject;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\Exception\NestedItemMatchedException;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\MessageWithNoTryAttribute;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\NestedItem;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\PropertyExceptionMappingBlueprint
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Iterable\IterablePropertyExceptionMatcherAggregate
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Iterable\ItemOfIterableExceptionMappingNode
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Type\Specification\MixedIterableType
 */
final class PropertyBlueprintIterableItemsUnitTest extends TestCase
{
    /** @var ExceptionMapper<ExceptionMatchList> */
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionMapperExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMapper<ExceptionMatchList> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<'.ExceptionMatchList::class.'>');
        $this->mapper = $mapper;
    }

    public function testExceptionCanBeCaughtOnNestedArrayItems(): void
    {
        $message = RootObject::create()->withNestedArrayItems([
            new NestedItem(41),
            new NestedItem(57),
            new NestedItem(32),
        ]);
        $originalException = new NestedItemMatchedException(code: 57);

        $exceptionMatches = $this->mapper->map($originalException, $message);

        self::assertNotNull($exceptionMatches);
        self::assertCount(1, $exceptionMatches);

        [$exceptionMatch] = $exceptionMatches->toArray();

        self::assertSame('nestedArrayItems[1].property', $exceptionMatch->getNode()->getPropertyPath()->join('.'));
    }

    public function testExceptionCanBeCaughtOnANestedIterableItems(): void
    {
        $message = RootObject::create()->withNestedIterableItems(new ArrayObject([
            'first' => new NestedItem(1),
            'second' => new NestedItem(2),
            'third' => new NestedItem(3),
            4 => new NestedItem(2),
        ]));
        $originalException = new NestedItemMatchedException(code: 3);

        $exceptionMatches = $this->mapper->map($originalException, $message);

        self::assertNotNull($exceptionMatches);
        self::assertCount(1, $exceptionMatches);

        [$exceptionMatch] = $exceptionMatches->toArray();
        self::assertSame('nestedIterableItems[third].property', $exceptionMatch->getNode()->getPropertyPath()->join('.'));
    }

    public function testExceptionCanBeCaughtOnMixedArrayItems(): void
    {
        $message = RootObject::create()->withNestedArrayItems([
            'not an object',
            new MessageWithNoTryAttribute(1),
            new NestedItem(2),
            new MessageWithNoTryAttribute(3),
        ]);
        $originalException = new NestedItemMatchedException(code: 2);

        $exceptionMatches = $this->mapper->map($originalException, $message);

        self::assertNotNull($exceptionMatches);
        self::assertCount(1, $exceptionMatches);

        [$exceptionMatch] = $exceptionMatches->toArray();
        self::assertSame('nestedArrayItems[2].property', $exceptionMatch->getNode()->getPropertyPath()->join('.'));
    }
}
