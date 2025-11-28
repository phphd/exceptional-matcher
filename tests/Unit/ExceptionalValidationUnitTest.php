<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit;

use ArrayObject;
use InvalidArgumentException;
use LogicException;
use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\CustomExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Email;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\CompositeException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\CompositeExceptionUnwrapper;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\ConditionallyCapturedException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\CustomFormattedException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\MessageContainingException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\NestedItemCapturedException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\NestedPropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\ObjectPropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\PropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\SomeValueException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\StaticPropertyCapturedException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\ViolationListExampleException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\NestedHandleableMessage;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\NestedItem;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\NotHandleableMessageStub;
use PhPhD\ExceptionToolkit\Unwrapper\PassThroughExceptionUnwrapper;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_flip;
use function array_intersect_key;

/**
 * @covers \PhPhD\ExceptionalValidation
 * @covers \PhPhD\ExceptionalValidation\Capture
 * @covers \PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension
 * @covers \PhPhD\ExceptionalValidation\Mapper\DefaultExceptionMapper
 * @covers \PhPhD\ExceptionalValidation\Mapper\Validator\ExceptionViolationListMapper
 * @covers \PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\List\DefaultExceptionListViolationFormatter
 * @covers \PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Delegating\DelegatingExceptionViolationFormatter
 * @covers \PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Default\DefaultExceptionViolationFormatter
 * @covers \PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ViolationList\ViolationListExceptionFormatter
 * @covers \PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Validator\ValidationFailedExceptionFormatter
 * @covers \PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Validator\ValidationFailedExceptionAdapter
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\ObjectRuleSet
 * @covers \PhPhD\ExceptionalValidation\Rule\ItemOfIterableCaptureRule
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyRuleSet
 * @covers \PhPhD\ExceptionalValidation\Rule\CompositeRuleSet
 * @covers \PhPhD\ExceptionalValidation\Rule\LazyRuleSet
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Path\PropertyPath
 * @covers \PhPhD\ExceptionalValidation\Rule\Exception\ExceptionPackage
 * @covers \PhPhD\ExceptionalValidation\Rule\Exception\CapturedException
 * @covers \PhPhD\ExceptionalValidation\Rule\Assembler\CompositeRuleSetAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssembler
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssembler
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyRulesAssembler
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidObjectRuleAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidIterableRulesAssemblerService
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\CaptureExceptionRule
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Class\ExceptionClassMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Class\ExceptionClassMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Origin\ExceptionOriginMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Origin\ExceptionOriginMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Delegating\DelegatingMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\ClosureMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\ClosureMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite\CompositeMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite\CaptureMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionMatchConditionFactory
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

        $container = PhdExceptionalValidationExtension::getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
            'phd_exceptional_validation.translation_domain' => 'domain',
        ], true);

        $translator = $this->createMock(TranslatorInterface::class);
        $translations = [
            '' => '',
            'oops' => 'oops - translated',
            'nested.message' => 'nested.message - translated',
            'This is the message to be used' => 'This is the message to be used',
        ];
        $translator->method('trans')
            ->willReturnCallback(static fn (string $id): string => $translations[$id] ?? $id)
        ;
        $container->set('translator', $translator);

        $container->register(CustomExceptionViolationFormatter::class, CustomExceptionViolationFormatter::class)
            ->setArguments([new Reference(ExceptionViolationFormatter::class.'<Throwable>')])
            ->setAutoconfigured(true)
        ;

        $exceptionUnwrapper = new CompositeExceptionUnwrapper(new PassThroughExceptionUnwrapper());
        $container->set('phd_exception_toolkit.exception_unwrapper', $exceptionUnwrapper);

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

    public function testCapturesExceptionMappedToProperty(): void
    {
        $message = HandleableMessageStub::create();
        $originalException = new PropertyCapturableException();

        $violationList = $this->exceptionMapper->map($message, $originalException);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        /** @var ConstraintViolationInterface $violation */
        $violation = $violationList[0];
        self::assertSame('property', $violation->getPropertyPath());
        self::assertSame('oops - translated', $violation->getMessage());
        self::assertSame('oops', $violation->getMessageTemplate());
        self::assertSame($message, $violation->getRoot());
        self::assertSame([], $violation->getParameters());
        self::assertNull($violation->getInvalidValue());
    }

    public function testCollectsInitializedPropertyValue(): void
    {
        $message = HandleableMessageStub::create()->withMessageText('invalid text value');

        /** @var ConstraintViolationListInterface $violationList */
        $violationList = $this->exceptionMapper->map($message, new LogicException());

        /** @var ConstraintViolationInterface $violation */
        [$violation] = $violationList;

        self::assertSame('invalid text value', $violation->getInvalidValue());
    }

    public function testCollectsObjectInvalidValue(): void
    {
        $message = HandleableMessageStub::create()->withObjectProperty($object = new stdClass());

        $originalException = new ObjectPropertyCapturableException();

        /** @var ConstraintViolationListInterface $violationList */
        $violationList = $this->exceptionMapper->map($message, $originalException);

        /** @var ConstraintViolationInterface $violation */
        [$violation] = $violationList;

        self::assertSame($object, $violation->getInvalidValue());
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

    public function testDoesNotCaptureNestedObjectPropertyWhenNotInitialized(): void
    {
        $message = HandleableMessageStub::create();
        $exception = new NestedPropertyCapturableException();

        $violationList = $this->exceptionMapper->map($message, $exception);

        self::assertNull($violationList);
    }

    public function testDoesNotCaptureNestedObjectWhenValidAttributeIsMissing(): void
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

    public function testDoesntCaptureConditionalExceptionWhenConditionIsNotMet(): void
    {
        $message = HandleableMessageStub::create()->withConditionalMessage(11, 41);

        $originalException = new ConditionallyCapturedException(12);

        $violationList = $this->exceptionMapper->map($message, $originalException);

        self::assertNull($violationList);
    }

    public function testCaptureConditionalException(): void
    {
        $message = HandleableMessageStub::create()->withConditionalMessage(11, 41);

        $originalException = new ConditionallyCapturedException(41);

        $violationList = $this->exceptionMapper->map($message, $originalException);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        /** @var ConstraintViolationInterface $violation */
        $violation = $violationList[0];
        self::assertSame('nestedObject.conditionalMessage.secondProperty', $violation->getPropertyPath());
        self::assertSame(41, $violation->getInvalidValue());
    }

    public function testExceptionIsNotCapturedWhenNestedItemsPropertyIsWithoutGenericType(): void
    {
        $message = HandleableMessageStub::create()->withNotTypedArray([
            new NestedItem(1),
            new NestedItem(2),
            new NestedItem(3),
        ]);

        $originalException = new NestedItemCapturedException(code: 2);

        $violationList = $this->exceptionMapper->map($message, $originalException);

        self::assertNull($violationList);
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

    public function testCustomViolationFormatter(): void
    {
        $message = HandleableMessageStub::create();

        $originalException = new CustomFormattedException();

        $violationList = $this->exceptionMapper->map($message, $originalException);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        /** @var ConstraintViolationInterface $violation */
        $violation = $violationList[0];
        self::assertSame('custom - oops - translated', $violation->getMessage());
        self::assertSame('custom.oops', $violation->getMessageTemplate());
        self::assertSame([
            'custom' => 'param',
        ], $violation->getParameters());
        self::assertSame('customFormatted', $violation->getPropertyPath());
    }

    public function testValueExceptionCondition(): void
    {
        $message = HandleableMessageStub::create();

        $exceptionAdapter = new CompositeException([
            new SomeValueException('matched!'),
            new SomeValueException('whatever'),
        ]);

        $violationList = $this->exceptionMapper->map($message, $exceptionAdapter);

        self::assertNotNull($violationList);
        self::assertCount(2, $violationList);

        /** @var ConstraintViolationInterface $violation1 */
        $violation1 = $violationList[0];

        self::assertSame('matchedProperty', $violation1->getPropertyPath());

        /** @var ConstraintViolationInterface $violation2 */
        $violation2 = $violationList[1];

        self::assertSame('anotherMatchedAsNoCondition', $violation2->getPropertyPath());
    }

    public function testViolationMessageFallsBackToExceptionMessage(): void
    {
        $message = HandleableMessageStub::create();
        $exceptionAdapter = new CompositeException([
            new MessageContainingException(),
            new MessageContainingException(),
        ]);

        $violationList = $this->exceptionMapper->map($message, $exceptionAdapter);

        self::assertNotNull($violationList);
        self::assertCount(2, $violationList);

        /** @var ConstraintViolationInterface $violation1 */
        $violation1 = $violationList[0];

        self::assertSame('fallBackToExceptionMessage', $violation1->getPropertyPath());
        self::assertSame('This is the message to be used', $violation1->getMessage());

        /** @var ConstraintViolationInterface $violation2 */
        $violation2 = $violationList[1];

        // When the message is specified as an empty string, empty message is used (w/o fallback)
        self::assertSame('emptyTranslationMessage', $violation2->getPropertyPath());
        self::assertSame('', $violation2->getMessage());
    }

    public function testValidatorViolationListExceptionMapping(): void
    {
        $message = HandleableMessageStub::create()->withNestedObject(new NestedHandleableMessage());

        $violationList = Validation::createValidator()->validate('123', [$constraint = new Length(max: 2)]);

        $originalException = new ViolationListExampleException($violationList);

        $violationList = $this->exceptionMapper->map($message, $originalException);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        $violation = $violationList[0];
        self::assertInstanceOf(ConstraintViolation::class, $violation);
        self::assertSame(
            'This value is too long. It should have 2 characters or less.',
            $violation->getMessage(),
        );
        self::assertSame(
            'This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.',
            $violation->getMessageTemplate(),
        );

        $parameters = array_intersect_key(
            $violation->getParameters(),
            array_flip(['{{ value }}', '{{ limit }}']),
        );
        self::assertSame([
            '{{ value }}' => '"123"',
            '{{ limit }}' => '2',
        ], $parameters);

        self::assertSame(2, $violation->getPlural());
        self::assertSame($message, $violation->getRoot());
        self::assertSame('nestedObject.violationListCapturedProperty', $violation->getPropertyPath());
        self::assertSame('123', $violation->getInvalidValue());
        self::assertSame(Length::TOO_LONG_ERROR, $violation->getCode());
        self::assertSame($constraint, $violation->getConstraint());
        self::assertNull($violation->getCause());
    }

    public function testValidationFailedExceptionCanBeCaptured(): void
    {
        $message = HandleableMessageStub::create();

        $validation = Validation::createCallable($constraint = new Length(min: 11));
        $originalException = null;

        try {
            $validation('matched!');
        } catch (ValidationFailedException $originalException) {
        }

        self::assertNotNull($originalException);

        $violationList = $this->exceptionMapper->map($message, $originalException);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        $violation = $violationList[0];
        self::assertInstanceOf(ConstraintViolation::class, $violation);
        self::assertSame(
            'This value is too short. It should have 11 characters or more.',
            $violation->getMessage(),
        );
        self::assertSame($constraint, $violation->getConstraint());
        self::assertSame('matchedProperty', $violation->getPropertyPath());
        self::assertSame('matched!', $violation->getInvalidValue());
    }

    public function testMatchExceptionByOriginClass(): void
    {
        $originalException = null;

        try {
            Email::fromString('non-email');
        } catch (ValidationFailedException $originalException) {
        }

        self::assertNotNull($originalException);

        $message = HandleableMessageStub::create();

        $violationList = $this->exceptionMapper->map($message, $originalException);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        $violation = $violationList[0];
        self::assertInstanceOf(ConstraintViolation::class, $violation);
        self::assertSame('email', $violation->getPropertyPath());
    }

    public function testMatchExceptionByOriginClassMethod(): void
    {
        $message = HandleableMessageStub::create();

        $originalException = null;

        try {
            Uuid::fromString('invalid-uuid');
        } catch (InvalidArgumentException $originalException) {
        }

        self::assertNotNull($originalException);

        $violationList = $this->exceptionMapper->map($message, $originalException);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        $violation = $violationList[0];
        self::assertInstanceOf(ConstraintViolation::class, $violation);
        self::assertSame('uid', $violation->getPropertyPath());
    }
}
