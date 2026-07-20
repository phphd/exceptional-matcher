<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests;

use ArrayObject;
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Exception\MatchedExceptionList;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub\RootObject;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\NestedItemMatchedException;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\NestedItem;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\NotHandleableMessageStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyMappingPlan
 */
final class PropertyPlanIterableItemsUnitTest extends TestCase
{
    /** @var ExceptionMatcher<MatchedExceptionList> */
    private ExceptionMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMatcher<MatchedExceptionList> $matcher */
        $matcher = $container->get(ExceptionMatcher::class.'<'.MatchedExceptionList::class.'>');
        $this->matcher = $matcher;
    }

    public function testExceptionCanBeCaughtOnNestedArrayItems(): void
    {
        $message = HandleableMessageStub::create()->withNestedArrayItems([
            new NestedItem(41),
            new NestedItem(57),
            new NestedItem(32),
        ]);
        $originalException = new NestedItemMatchedException(code: 57);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('nestedArrayItems[1].property', $matchedException->getRule()->getPropertyPath()->join('.'));
    }

    public function testExceptionCanBeCaughtOnANestedIterableItems(): void
    {
        $message = HandleableMessageStub::create()->withNestedIterableItems(new ArrayObject([
            'first' => new NestedItem(1),
            'second' => new NestedItem(2),
            'third' => new NestedItem(3),
            4 => new NestedItem(2),
        ]));
        $originalException = new NestedItemMatchedException(code: 3);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();
        self::assertSame('nestedIterableItems[third].property', $matchedException->getRule()->getPropertyPath()->join('.'));
    }

    public function testExceptionCanBeCaughtOnMixedArrayItems(): void
    {
        $message = RootObject::create()->withNotTypedArray([
            'not an object',
            new NotHandleableMessageStub(1),
            new NestedItem(2),
            new NotHandleableMessageStub(3),
        ]);
        $originalException = new NestedItemMatchedException(code: 2);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();
        self::assertSame('notTypedArray[2].property', $matchedException->getRule()->getPropertyPath()->join('.'));
    }
}
