<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Tests\Unit;

use ArrayObject;
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Main\Tests\Stub\ObjectPropertyMatchedException;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\AnException;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\CompositeException;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\CompositeExceptionUnwrapper;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\NestedItemMatchedException;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\NestedPropertyMatchedException;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\StaticPropertyMatchedException;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\NestedHandleableMessage;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\NestedItem;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\NotHandleableMessageStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Try_
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_
 * @covers \PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension
 * @covers \PhPhD\ExceptionalMatcher\MainExceptionMatcher
 * @covers \PhPhD\ExceptionalMatcher\Integration\Validator\ExceptionToViolationListMatcher
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\ObjectMatchingRuleSet
 * @covers \PhPhD\ExceptionalMatcher\Rule\ItemOfIterableMatchingRule
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyMatchingRuleSet
 * @covers \PhPhD\ExceptionalMatcher\Rule\CompositeMatchingRule
 * @covers \PhPhD\ExceptionalMatcher\Rule\LazyMatchingRule
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Path\PropertyPath
 * @covers \PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal
 * @covers \PhPhD\ExceptionalMatcher\Exception\MatchedException
 * @covers \PhPhD\ExceptionalMatcher\Exception\MatchedExceptionList
 * @covers \PhPhD\ExceptionalMatcher\Rule\Assembler\CompositeRuleSetAssemblerService
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Assembler\ObjectMatchingRuleSetAssembler
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Assembler\ObjectMatchingRuleSetAssemblerService
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler\PropertyMatchingRuleSetAssembler
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler\PropertyMatchingRuleSetAssemblerService
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Assembler\PropertyMatchingRulesAssembler
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Assembler\PropertyMatchingRulesAssemblerService
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler\Rules\PropertyNestedValidObjectRuleAssemblerService
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler\Rules\PropertyNestedValidIterableRulesAssemblerService
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\MatchExceptionRule
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Class\ExceptionClassMatchCondition
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Class\ExceptionClassMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\PreCompiledMatchConditionBlueprint
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Delegating\DelegatingMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\CompositeMatchCondition
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\CompositeMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\CompositeMatchConditionBlueprint
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\ReusableIteratorAggregate
 *
 * @internal
 */
final class ExceptionMatcherUnitTest extends TestCase
{
    /** @var ExceptionMatcher<ConstraintViolationListInterface> */
    private ExceptionMatcher $exceptionMatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
            'phd_exceptional_matcher.translation_domain' => 'domain',
        ]);

        $translator = $this->createMock(TranslatorInterface::class);
        $translations = [
            'domain' => [
                'nested.message' => 'nested.message - translated',
            ],
        ];
        $translator->method('trans')
            ->willReturnCallback(static fn (string $id, array $params, string $domain): string => $translations[$domain][$id] ?? $id)
        ;
        $container->set('translator', $translator);

        $container
            ->register(CompositeExceptionUnwrapper::class, CompositeExceptionUnwrapper::class)
            ->setArguments([new Reference('.inner')])
            ->setDecoratedService('phd_exception_toolkit.exception_unwrapper.stack')
        ;

        $container->compile();

        /** @var ExceptionMatcher<ConstraintViolationListInterface> $matcher */
        $matcher = $container->get(ExceptionMatcher::class.'<'.ConstraintViolationListInterface::class.'>');
        $this->exceptionMatcher = $matcher;
    }

    public function testExceptionIsNotCapturedForMessageWithoutExceptionalValidationAttribute(): void
    {
        $message = new NotHandleableMessageStub(123);
        $exception = new AnException();

        $violationList = $this->exceptionMatcher->match($exception, $message);

        self::assertNull($violationList);
    }

    public function testCaptureExceptionMappedToProperty(): void
    {
        $message = HandleableMessageStub::create();
        $originalException = new AnException();

        $violationList = $this->exceptionMatcher->match($originalException, $message);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        /** @var ConstraintViolationInterface $violation */
        $violation = $violationList[0];
        self::assertSame('property', $violation->getPropertyPath());
    }

    public function testCaptureExceptionMappedToStaticProperty(): void
    {
        $message = HandleableMessageStub::create();
        $originalException = new StaticPropertyMatchedException();

        /** @var ConstraintViolationListInterface $violationList */
        $violationList = $this->exceptionMatcher->match($originalException, $message);

        /** @var ConstraintViolationInterface $violation */
        [$violation] = $violationList;

        self::assertSame('staticProperty', $violation->getPropertyPath());
        self::assertSame('foo', $violation->getInvalidValue());
    }

    public function testNestedObjectIsNotCapturedWhenPropertyIsNotInitialized(): void
    {
        $message = HandleableMessageStub::create();
        $exception = new NestedPropertyMatchedException();

        $violationList = $this->exceptionMatcher->match($exception, $message);

        self::assertNull($violationList);
    }

    public function testCaptureNestedObjectPropertyException(): void
    {
        $message = HandleableMessageStub::create()->withNestedObject(new NestedHandleableMessage());
        $originalException = new NestedPropertyMatchedException();

        $violationList = $this->exceptionMatcher->match($originalException, $message);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        /** @var ConstraintViolationInterface $violation */
        $violation = $violationList[0];

        self::assertSame('nested.message - translated', $violation->getMessage());
        self::assertSame('nested.message', $violation->getMessageTemplate());
        self::assertSame('nestedObject.nestedProperty', $violation->getPropertyPath());
        self::assertNull($violation->getInvalidValue());
    }

    public function testUncaughtExceptionsAreNotAllowed(): void
    {
        $message = HandleableMessageStub::create()
            ->withNestedArrayItems([
                'first' => new NestedItem(1),
                'second' => new NestedItem(2),
            ])
        ;

        $exceptionAdapter = new CompositeException([
            new NestedItemMatchedException(code: 1),
            new NestedItemMatchedException(code: 3), // not caught
        ]);

        $violationList = $this->exceptionMatcher->match($exceptionAdapter, $message);

        self::assertNull($violationList);
    }

    public function testCaptureMultipleExceptions(): void
    {
        $message = HandleableMessageStub::create()
            ->withNestedArrayItems([
                'first' => new NestedItem(2),
            ])
            ->withNestedIterableItems(new ArrayObject([
                'second' => new NestedItem(1),
            ]))
        ;

        $exceptionAdapter = new CompositeException([
            new NestedItemMatchedException(code: 1),
            new AnException(),
            new ObjectPropertyMatchedException(),
            new NestedItemMatchedException(code: 2),
        ]);

        $violationList = $this->exceptionMatcher->match($exceptionAdapter, $message);

        self::assertNotNull($violationList);
        self::assertCount(4, $violationList);

        /** @var ConstraintViolationInterface $firstViolation */
        $firstViolation = $violationList[0];
        self::assertSame('property', $firstViolation->getPropertyPath());

        /** @var ConstraintViolationInterface $secondViolation */
        $secondViolation = $violationList[1];
        self::assertSame('objectProperty', $secondViolation->getPropertyPath());

        /** @var ConstraintViolationInterface $thirdViolation */
        $thirdViolation = $violationList[2];
        self::assertSame('nestedArrayItems[first].property', $thirdViolation->getPropertyPath());

        /** @var ConstraintViolationInterface $fourthViolation */
        $fourthViolation = $violationList[3];
        self::assertSame('nestedIterableItems[second].property', $fourthViolation->getPropertyPath());
    }
}
