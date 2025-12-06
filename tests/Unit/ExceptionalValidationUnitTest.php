<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit;

use ArrayObject;
use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Main\Tests\Stub\ObjectPropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\CompositeException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\CompositeExceptionUnwrapper;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\NestedItemCapturedException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\NestedPropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\PropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\StaticPropertyCapturedException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\NestedHandleableMessage;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\NestedItem;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\NotHandleableMessageStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @covers \PhPhD\ExceptionalValidation
 * @covers \PhPhD\ExceptionalValidation\Capture
 * @covers \PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension
 * @covers \PhPhD\ExceptionalValidation\Mapper\MainExceptionMapper
 * @covers \PhPhD\ExceptionalValidation\Mapper\Validator\ExceptionToViolationListMapper
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\ObjectRuleSet
 * @covers \PhPhD\ExceptionalValidation\Rule\ItemOfIterableCaptureRule
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyRuleSet
 * @covers \PhPhD\ExceptionalValidation\Rule\CompositeRuleSet
 * @covers \PhPhD\ExceptionalValidation\Rule\LazyRuleSet
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Path\PropertyPath
 * @covers \PhPhD\ExceptionalValidation\Rule\Exception\ExceptionReciprocal
 * @covers \PhPhD\ExceptionalValidation\Rule\Exception\MatchedException
 * @covers \PhPhD\ExceptionalValidation\Rule\Exception\MatchedExceptionList
 * @covers \PhPhD\ExceptionalValidation\Rule\Assembler\CompositeRuleSetAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssembler
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssembler
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssembler
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidObjectRuleAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidIterableRulesAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\CaptureExceptionRule
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Class\ExceptionClassMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Class\ExceptionClassMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Delegating\DelegatingMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite\CompositeMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite\CompositeMatchConditionFactory
 *
 * @internal
 */
final class ExceptionalValidationUnitTest extends TestCase
{
    /** @var ExceptionMapper<ConstraintViolationListInterface> */
    private ExceptionMapper $exceptionMapper;

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

        /** @var ExceptionMapper<ConstraintViolationListInterface> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>');
        $this->exceptionMapper = $mapper;
    }

    public function testExceptionIsNotCapturedForMessageWithoutExceptionalValidationAttribute(): void
    {
        $message = new NotHandleableMessageStub(123);

        $violationList = $this->exceptionMapper->map($message, new PropertyCapturableException());

        self::assertNull($violationList);
    }

    public function testCaptureExceptionMappedToProperty(): void
    {
        $message = HandleableMessageStub::create();
        $originalException = new PropertyCapturableException();

        $violationList = $this->exceptionMapper->map($message, $originalException);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        /** @var ConstraintViolationInterface $violation */
        $violation = $violationList[0];
        self::assertSame('property', $violation->getPropertyPath());
    }

    public function testCaptureExceptionMappedToStaticProperty(): void
    {
        $message = HandleableMessageStub::create();
        $originalException = new StaticPropertyCapturedException();

        /** @var ConstraintViolationListInterface $violationList */
        $violationList = $this->exceptionMapper->map($message, $originalException);

        /** @var ConstraintViolationInterface $violation */
        [$violation] = $violationList;

        self::assertSame('staticProperty', $violation->getPropertyPath());
        self::assertSame('foo', $violation->getInvalidValue());
    }

    public function testNestedObjectIsNotCapturedWhenPropertyIsNotInitialized(): void
    {
        $message = HandleableMessageStub::create();
        $exception = new NestedPropertyCapturableException();

        $violationList = $this->exceptionMapper->map($message, $exception);

        self::assertNull($violationList);
    }

    public function testNestedObjectIsNotCapturedWhenValidAttributeIsMissing(): void
    {
        $message = HandleableMessageStub::create()->withOrdinaryObject(new NestedHandleableMessage());
        $exception = new NestedPropertyCapturableException();

        $violationList = $this->exceptionMapper->map($message, $exception);

        self::assertNull($violationList);
    }

    public function testCaptureNestedObjectPropertyException(): void
    {
        $message = HandleableMessageStub::create()->withNestedObject(new NestedHandleableMessage());

        $originalException = new NestedPropertyCapturableException();

        $violationList = $this->exceptionMapper->map($message, $originalException);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        /** @var ConstraintViolationInterface $violation */
        $violation = $violationList[0];

        self::assertSame('nested.message - translated', $violation->getMessage());
        self::assertSame('nested.message', $violation->getMessageTemplate());
        self::assertSame('nestedObject.nestedProperty', $violation->getPropertyPath());
        self::assertNull($violation->getInvalidValue());
    }

    public function testExceptionIsNotCapturedWhenNestedItemsValueTypeClassIsNotMarkedWithExceptionalValidationAttribute(): void
    {
        /**
         * @noinspection PhpParamsInspection
         *
         * @psalm-suppress InvalidArgument
         */
        $message = HandleableMessageStub::create()->withTypedNotHandleableArray([ // @phpstan-ignore argument.type
            new NestedItem(1), // deliberately passing incorrect objects
            new NestedItem(2),
            new NestedItem(3),
        ]);

        $originalException = new NestedItemCapturedException(code: 2);

        $violationList = $this->exceptionMapper->map($message, $originalException);

        self::assertNull($violationList);
    }

    public function testCanCaptureExceptionOnNestedArrayItemWhenPropertyIsMarkedWithValidAttribute(): void
    {
        $message = HandleableMessageStub::create()->withNestedArrayItems([
            new NestedItem(41),
            new NestedItem(57),
            new NestedItem(32),
        ]);

        $originalException = new NestedItemCapturedException(code: 57);

        $violationList = $this->exceptionMapper->map($message, $originalException);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        /** @var ConstraintViolationInterface $violation */
        $violation = $violationList[0];
        self::assertSame('nestedArrayItems[1].property', $violation->getPropertyPath());
    }

    public function testCanCaptureExceptionOnANestedIterableItemWhenPropertyIsMarkedWithValidAttribute(): void
    {
        $message = HandleableMessageStub::create()->withNestedIterableItems(new ArrayObject([
            'first' => new NestedItem(1),
            'second' => new NestedItem(2),
            'third' => new NestedItem(3),
            4 => new NestedItem(2),
        ]));

        $originalException = new NestedItemCapturedException(code: 2);

        $violationList = $this->exceptionMapper->map($message, $originalException);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        /** @var ConstraintViolationInterface $firstViolation */
        $firstViolation = $violationList[0];
        self::assertSame('nestedIterableItems[second].property', $firstViolation->getPropertyPath());
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
            new NestedItemCapturedException(code: 1),
            new NestedItemCapturedException(code: 3), // not caught
        ]);

        $violationList = $this->exceptionMapper->map($message, $exceptionAdapter);

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
            new NestedItemCapturedException(code: 1),
            new PropertyCapturableException(),
            new ObjectPropertyCapturableException(),
            new NestedItemCapturedException(code: 2),
        ]);

        $violationList = $this->exceptionMapper->map($message, $exceptionAdapter);

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
