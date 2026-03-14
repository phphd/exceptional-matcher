<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\Tests;

use ArrayObject;
use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Matcher\ExceptionMatcher;
use PhPhD\ExceptionalValidation\Rule\Exception\MatchedExceptionList;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\Tests\Stub\RootObject;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\NestedItemCapturedException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\NestedItem;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\NotHandleableMessageStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidIterableRulesAssemblerService
 */
final class PropertyNestedValidIterableRulesAssemblerServiceUnitTest extends TestCase
{
    /** @var ExceptionMatcher<MatchedExceptionList> */
    private ExceptionMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalValidationExtension())->getContainer([
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
        $originalException = new NestedItemCapturedException(code: 57);
        $message = HandleableMessageStub::create()->withNestedArrayItems([
            new NestedItem(41),
            new NestedItem(57),
            new NestedItem(32),
        ]);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('nestedArrayItems[1].property', $matchedException->getRule()->getPropertyPath()->join('.'));
    }

    public function testExceptionCanBeCaughtOnANestedIterableItems(): void
    {
        $originalException = new NestedItemCapturedException(code: 3);
        $message = HandleableMessageStub::create()->withNestedIterableItems(new ArrayObject([
            'first' => new NestedItem(1),
            'second' => new NestedItem(2),
            'third' => new NestedItem(3),
            4 => new NestedItem(2),
        ]));

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();
        self::assertSame('nestedIterableItems[third].property', $matchedException->getRule()->getPropertyPath()->join('.'));
    }

    public function testExceptionCanBeCaughtOnMixedArrayItems(): void
    {
        $originalException = new NestedItemCapturedException(code: 2);
        $message = RootObject::create()->withNotTypedArray([
            'not an object',
            new NotHandleableMessageStub(1),
            new NestedItem(2),
            new NotHandleableMessageStub(3),
        ]);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();
        self::assertSame('notTypedArray[2].property', $matchedException->getRule()->getPropertyPath()->join('.'));
    }
}
