<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit;

use ArrayObject;
use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\ExceptionMatcher;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\AnException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\CompositeException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\CompositeExceptionUnwrapper;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\NestedItemMatchedException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\NestedPropertyMatchedException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\StaticPropertyMatchedException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\NestedHandleableMessage;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\NestedItem;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\NotHandleableMessageStub;
use PhPhD\ExceptionalValidation\Validator\Formatter\Main\Tests\Stub\ObjectPropertyMatchedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Try_
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Catch_
 * @covers \PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension
 * @covers \PhPhD\ExceptionalValidation\MainExceptionMatcher
 * @covers \PhPhD\ExceptionalValidation\Validator\ExceptionToViolationListMatcher
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\ObjectMatchingRuleSet
 * @covers \PhPhD\ExceptionalValidation\Rule\ItemOfIterableMatchingRule
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyMatchingRuleSet
 * @covers \PhPhD\ExceptionalValidation\Rule\CompositeMatchingRule
 * @covers \PhPhD\ExceptionalValidation\Rule\LazyMatchingRule
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Path\PropertyPath
 * @covers \PhPhD\ExceptionalValidation\Rule\Exception\ExceptionReciprocal
 * @covers \PhPhD\ExceptionalValidation\Rule\Exception\MatchedException
 * @covers \PhPhD\ExceptionalValidation\Rule\Exception\MatchedExceptionList
 * @covers \PhPhD\ExceptionalValidation\Rule\Assembler\CompositeRuleSetAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectMatchingRuleSetAssembler
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectMatchingRuleSetAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyMatchingRuleSetAssembler
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyMatchingRuleSetAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Assembler\PropertyMatchingRulesAssembler
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Assembler\PropertyMatchingRulesAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidObjectRuleAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidIterableRulesAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Match\MatchExceptionRule
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\Class\ExceptionClassMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\Class\ExceptionClassMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\Delegating\DelegatingMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\Composite\CompositeMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\Composite\CompositeMatchConditionFactory
 *
 * @internal
 */
final class ExceptionalValidationUnitTest extends TestCase
{
    /** @var ExceptionMatcher<ConstraintViolationListInterface> */
    private ExceptionMatcher $exceptionMatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalValidationExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
            'phd_exceptional_validation.translation_domain' => 'domain',
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
        $exception = new AnException();
        $message = new NotHandleableMessageStub(123);

        $violationList = $this->exceptionMatcher->match($exception, $message);

        self::assertNull($violationList);
    }

    public function testCaptureExceptionMappedToProperty(): void
    {
        $originalException = new AnException();
        $message = HandleableMessageStub::create();

        $violationList = $this->exceptionMatcher->match($originalException, $message);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        /** @var ConstraintViolationInterface $violation */
        $violation = $violationList[0];
        self::assertSame('property', $violation->getPropertyPath());
    }

    public function testCaptureExceptionMappedToStaticProperty(): void
    {
        $originalException = new StaticPropertyMatchedException();
        $message = HandleableMessageStub::create();

        /** @var ConstraintViolationListInterface $violationList */
        $violationList = $this->exceptionMatcher->match($originalException, $message);

        /** @var ConstraintViolationInterface $violation */
        [$violation] = $violationList;

        self::assertSame('staticProperty', $violation->getPropertyPath());
        self::assertSame('foo', $violation->getInvalidValue());
    }

    public function testNestedObjectIsNotCapturedWhenPropertyIsNotInitialized(): void
    {
        $exception = new NestedPropertyMatchedException();
        $message = HandleableMessageStub::create();

        $violationList = $this->exceptionMatcher->match($exception, $message);

        self::assertNull($violationList);
    }

    public function testCaptureNestedObjectPropertyException(): void
    {
        $originalException = new NestedPropertyMatchedException();
        $message = HandleableMessageStub::create()->withNestedObject(new NestedHandleableMessage());

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
        $exceptionAdapter = new CompositeException([
            new NestedItemMatchedException(code: 1),
            new NestedItemMatchedException(code: 3), // not caught
        ]);

        $message = HandleableMessageStub::create()
            ->withNestedArrayItems([
                'first' => new NestedItem(1),
                'second' => new NestedItem(2),
            ])
        ;

        $violationList = $this->exceptionMatcher->match($exceptionAdapter, $message);

        self::assertNull($violationList);
    }

    public function testCaptureMultipleExceptions(): void
    {
        $exceptionAdapter = new CompositeException([
            new NestedItemMatchedException(code: 1),
            new AnException(),
            new ObjectPropertyMatchedException(),
            new NestedItemMatchedException(code: 2),
        ]);

        $message = HandleableMessageStub::create()
            ->withNestedArrayItems([
                'first' => new NestedItem(2),
            ])
            ->withNestedIterableItems(new ArrayObject([
                'second' => new NestedItem(1),
            ]))
        ;

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
