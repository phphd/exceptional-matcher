<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit;

use ArrayObject;
use LogicException;
use PhPhD\ExceptionalValidation\Formatter\Item\Default\DefaultExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Formatter\Item\Delegating\DelegatingExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Formatter\Item\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Formatter\Item\Validator\ValidationFailedExceptionFormatter;
use PhPhD\ExceptionalValidation\Formatter\Item\ViolationList\ViolationListExceptionFormatter;
use PhPhD\ExceptionalValidation\Formatter\List\DefaultExceptionListViolationFormatter;
use PhPhD\ExceptionalValidation\Handler\DefaultExceptionHandler;
use PhPhD\ExceptionalValidation\Handler\Exception\ExceptionalValidationFailedException;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssembler;
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
use Psr\Container\ContainerInterface;
use stdClass;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_flip;
use function array_intersect_key;

/**
 * @covers \PhPhD\ExceptionalValidation
 * @covers \PhPhD\ExceptionalValidation\Capture
 * @covers \PhPhD\ExceptionalValidation\Handler\DefaultExceptionHandler
 * @covers \PhPhD\ExceptionalValidation\Handler\Exception\ExceptionalValidationFailedException
 * @covers \PhPhD\ExceptionalValidation\Formatter\List\DefaultExceptionListViolationFormatter
 * @covers \PhPhD\ExceptionalValidation\Formatter\Item\Delegating\DelegatingExceptionViolationFormatter
 * @covers \PhPhD\ExceptionalValidation\Formatter\Item\Default\DefaultExceptionViolationFormatter
 * @covers \PhPhD\ExceptionalValidation\Formatter\Item\ViolationList\ViolationListExceptionFormatter
 * @covers \PhPhD\ExceptionalValidation\Formatter\Item\Validator\ValidationFailedExceptionFormatter
 * @covers \PhPhD\ExceptionalValidation\Formatter\Item\Validator\ValidationFailedExceptionAdapter
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\ObjectRuleSet
 * @covers \PhPhD\ExceptionalValidation\Rule\ItemOfIterableCaptureRule
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyRuleSet
 * @covers \PhPhD\ExceptionalValidation\Rule\CompositeRuleSet
 * @covers \PhPhD\ExceptionalValidation\Rule\LazyRuleSet
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Path\PropertyPath
 * @covers \PhPhD\ExceptionalValidation\Rule\Exception\ExceptionPackage
 * @covers \PhPhD\ExceptionalValidation\Rule\Exception\CapturedException
 * @covers \PhPhD\ExceptionalValidation\Rule\Assembler\CompositeRuleSetAssembler
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssembler
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Assembler\Rules\ObjectRulesAssemblerEnvelope
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Assembler\Rules\ObjectRulesAssembler
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssemblerEnvelope
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssembler
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyRulesAssemblerEnvelope
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssembler
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidObjectRuleAssembler
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidIterableRulesAssembler
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Assembler\IterableOfObjectsRuleSetAssembler
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
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionValueMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionValueMatchConditionFactory
 *
 * @internal
 */
final class ExceptionalValidationUnitTest extends TestCase
{
    private DefaultExceptionHandler $exceptionHandler;

    protected function setUp(): void
    {
        parent::setUp();

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

        $defaultViolationFormatter = new DefaultExceptionViolationFormatter($translator, 'domain');
        $violationListExceptionFormatter = new ViolationListExceptionFormatter();
        $validationFailedExceptionFormatter = new ValidationFailedExceptionFormatter($violationListExceptionFormatter);
        $customViolationFormatter = new CustomExceptionViolationFormatter($defaultViolationFormatter);

        $formatterRegistry = $this->createMock(ContainerInterface::class);
        $formatters = [
            'default' => $defaultViolationFormatter,
            ViolationListExceptionFormatter::class => $violationListExceptionFormatter,
            ValidationFailedExceptionFormatter::class => $validationFailedExceptionFormatter,
            CustomExceptionViolationFormatter::class => $customViolationFormatter,
        ];
        $formatterRegistry->method('has')
            ->willReturnCallback(static fn (string $id): bool => isset($formatters[$id]))
        ;
        $formatterRegistry->method('get')
            ->willReturnCallback(static fn (string $id): ExceptionViolationFormatter => $formatters[$id])
        ;

        $objectRuleSetAssembler = ObjectRuleSetAssembler::create();
        $violationFormatter = new DelegatingExceptionViolationFormatter($formatterRegistry);
        $exceptionUnwrapper = new CompositeExceptionUnwrapper(new PassThroughExceptionUnwrapper());
        $violationListFormatter = new DefaultExceptionListViolationFormatter($violationFormatter);
        $this->exceptionHandler = new DefaultExceptionHandler($objectRuleSetAssembler, $exceptionUnwrapper, $violationListFormatter);
    }

    public function testDoesNotCaptureExceptionForMessageWithoutExceptionalValidationAttribute(): void
    {
        $message = new NotHandleableMessageStub(123);

        $this->expectNotToPerformAssertions();

        $this->exceptionHandler->capture($message, new PropertyCapturableException());
    }

    public function testCapturesExceptionMappedToProperty(): void
    {
        $message = HandleableMessageStub::create();
        $originalException = new PropertyCapturableException();

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, $originalException);
        } catch (ExceptionalValidationFailedException $e) {
            self::assertSame(
                'Message of type "PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub" has failed exceptional validation.',
                $e->getMessage(),
            );
            self::assertSame($originalException, $e->getPrevious());
            self::assertSame($message, $e->getViolatingMessage());

            $violationList = $e->getViolationList();
            self::assertCount(1, $violationList);

            /** @var ConstraintViolationInterface $violation */
            $violation = $violationList[0];
            self::assertSame('property', $violation->getPropertyPath());
            self::assertSame('oops - translated', $violation->getMessage());
            self::assertSame('oops', $violation->getMessageTemplate());
            self::assertSame($message, $violation->getRoot());
            self::assertSame([], $violation->getParameters());
            self::assertNull($violation->getInvalidValue());

            throw $e;
        }
    }

    public function testCollectsInitializedPropertyValue(): void
    {
        $message = HandleableMessageStub::create()->withMessageText('invalid text value');

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, new LogicException());
        } catch (ExceptionalValidationFailedException $e) {
            /** @var ConstraintViolationInterface $violation */
            [$violation] = $e->getViolationList();

            self::assertSame('invalid text value', $violation->getInvalidValue());

            throw $e;
        }
    }

    public function testCollectsObjectInvalidValue(): void
    {
        $message = HandleableMessageStub::create()->withObjectProperty($object = new stdClass());

        $this->expectException(ExceptionalValidationFailedException::class);

        $originalException = new ObjectPropertyCapturableException();

        try {
            $this->exceptionHandler->capture($message, $originalException);
        } catch (ExceptionalValidationFailedException $e) {
            /** @var ConstraintViolationInterface $violation */
            [$violation] = $e->getViolationList();

            self::assertSame($object, $violation->getInvalidValue());

            throw $e;
        }
    }

    public function testCaptureExceptionMappedToStaticProperty(): void
    {
        $message = HandleableMessageStub::create();

        $this->expectException(ExceptionalValidationFailedException::class);

        $originalException = new StaticPropertyCapturedException();

        try {
            $this->exceptionHandler->capture($message, $originalException);
        } catch (ExceptionalValidationFailedException $e) {
            /** @var ConstraintViolationInterface $violation */
            [$violation] = $e->getViolationList();

            self::assertSame('staticProperty', $violation->getPropertyPath());
            self::assertSame('foo', $violation->getInvalidValue());

            throw $e;
        }
    }

    public function testDoesNotCaptureNestedObjectPropertyWhenNotInitialized(): void
    {
        $message = HandleableMessageStub::create();

        $exception = new NestedPropertyCapturableException();

        $this->expectNotToPerformAssertions();

        $this->exceptionHandler->capture($message, $exception);
    }

    public function testDoesNotCaptureNestedObjectWhenValidAttributeIsMissing(): void
    {
        $message = HandleableMessageStub::create()->withOrdinaryObject(new NestedHandleableMessage());

        $exception = new NestedPropertyCapturableException();

        $this->expectNotToPerformAssertions();

        $this->exceptionHandler->capture($message, $exception);
    }

    public function testCaptureNestedObjectPropertyException(): void
    {
        $message = HandleableMessageStub::create()->withNestedObject(new NestedHandleableMessage());

        $originalException = new NestedPropertyCapturableException();

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, $originalException);
        } catch (ExceptionalValidationFailedException $e) {
            self::assertSame($originalException, $e->getPrevious());

            $violationList = $e->getViolationList();
            self::assertCount(1, $violationList);

            /** @var ConstraintViolationInterface $violation */
            $violation = $violationList[0];
            self::assertSame('nested.message - translated', $violation->getMessage());
            self::assertSame('nested.message', $violation->getMessageTemplate());
            self::assertSame('nestedObject.nestedProperty', $violation->getPropertyPath());
            self::assertNull($violation->getInvalidValue());

            throw $e;
        }
    }

    public function testDoesntCaptureConditionalExceptionWhenConditionIsNotMet(): void
    {
        $message = HandleableMessageStub::create()->withConditionalMessage(11, 41);

        $originalException = new ConditionallyCapturedException(12);

        $this->expectNotToPerformAssertions();

        $this->exceptionHandler->capture($message, $originalException);
    }

    public function testCaptureConditionalException(): void
    {
        $message = HandleableMessageStub::create()->withConditionalMessage(11, 41);

        $originalException = new ConditionallyCapturedException(41);

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, $originalException);
        } catch (ExceptionalValidationFailedException $e) {
            self::assertSame($originalException, $e->getPrevious());

            $violationList = $e->getViolationList();
            self::assertCount(1, $violationList);

            /** @var ConstraintViolationInterface $violation */
            $violation = $violationList[0];
            self::assertSame('nestedObject.conditionalMessage.secondProperty', $violation->getPropertyPath());
            self::assertSame(41, $violation->getInvalidValue());

            throw $e;
        }
    }

    public function testDoesNotCaptureExceptionOnNestedItemsWhenPropertyIsWithoutValidAttribute(): void
    {
        $message = HandleableMessageStub::create()->withJustArray([
            new NestedItem(1),
            new NestedItem(2),
            new NestedItem(3),
        ]);

        $originalException = new NestedItemCapturedException(code: 2);

        $this->expectNotToPerformAssertions();

        $this->exceptionHandler->capture($message, $originalException);
    }

    public function testCaptureExceptionOnNestedArrayItem(): void
    {
        $message = HandleableMessageStub::create()->withNestedArrayItems([
            new NestedItem(41),
            new NestedItem(57),
            new NestedItem(32),
        ]);

        $originalException = new NestedItemCapturedException(code: 57);

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, $originalException);
        } catch (ExceptionalValidationFailedException $e) {
            self::assertSame($originalException, $e->getPrevious());

            $violationList = $e->getViolationList();
            self::assertCount(1, $violationList);

            /** @var ConstraintViolationInterface $violation */
            $violation = $violationList[0];
            self::assertSame('nestedArrayItems[1].property', $violation->getPropertyPath());

            throw $e;
        }
    }

    public function testCaptureExceptionOnNestedIterableItem(): void
    {
        $message = HandleableMessageStub::create()->withNestedIterableItems(new ArrayObject([
            'first' => new NestedItem(1),
            'second' => new NestedItem(2),
            'third' => new NestedItem(3),
            4 => new NestedItem(2),
        ]));

        $originalException = new NestedItemCapturedException(code: 2);

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, $originalException);
        } catch (ExceptionalValidationFailedException $e) {
            self::assertSame($originalException, $e->getPrevious());

            $violationList = $e->getViolationList();
            self::assertCount(1, $violationList);

            /** @var ConstraintViolationInterface $firstViolation */
            $firstViolation = $violationList[0];
            self::assertSame('nestedIterableItems[second].property', $firstViolation->getPropertyPath());

            throw $e;
        }
    }

    public function testNotASingleUnhandledExceptionIsAllowed(): void
    {
        $message = HandleableMessageStub::create()
            ->withNestedArrayItems([
                'first' => new NestedItem(1),
                'second' => new NestedItem(2),
            ])
        ;

        $exceptionAdapter = new CompositeException([
            new NestedItemCapturedException(code: 1),
            new NestedItemCapturedException(code: 3),
        ]);

        $this->expectNotToPerformAssertions();

        $this->exceptionHandler->capture($message, $exceptionAdapter);
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

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, $exceptionAdapter);
        } catch (ExceptionalValidationFailedException $e) {
            $violationList = $e->getViolationList();
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

            throw $e;
        }
    }

    public function testCustomViolationFormatter(): void
    {
        $message = HandleableMessageStub::create();

        $this->expectException(ExceptionalValidationFailedException::class);

        $originalException = new CustomFormattedException();

        try {
            $this->exceptionHandler->capture($message, $originalException);
        } catch (ExceptionalValidationFailedException $e) {
            $violationList = $e->getViolationList();
            self::assertCount(1, $violationList);

            /** @var ConstraintViolationInterface $violation */
            $violation = $violationList[0];
            self::assertSame('custom - oops - translated', $violation->getMessage());
            self::assertSame('custom.oops', $violation->getMessageTemplate());
            self::assertSame([
                'custom' => 'param',
            ], $violation->getParameters());
            self::assertSame('customFormatted', $violation->getPropertyPath());

            throw $e;
        }
    }

    public function testValueExceptionCondition(): void
    {
        $message = HandleableMessageStub::create();

        $exceptionAdapter = new CompositeException([
            new SomeValueException('matched!'),
            new SomeValueException('whatever'),
        ]);

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, $exceptionAdapter);
        } catch (ExceptionalValidationFailedException $e) {
            $violationList = $e->getViolationList();
            self::assertCount(2, $violationList);

            /** @var ConstraintViolationInterface $violation1 */
            $violation1 = $violationList[0];

            self::assertSame('matchedProperty', $violation1->getPropertyPath());

            /** @var ConstraintViolationInterface $violation2 */
            $violation2 = $violationList[1];

            self::assertSame('anotherMatchedAsNoCondition', $violation2->getPropertyPath());

            throw $e;
        }
    }

    public function testViolationMessageFallsBackToExceptionMessage(): void
    {
        $message = HandleableMessageStub::create();
        $exceptionAdapter = new CompositeException([
            new MessageContainingException(),
            new MessageContainingException(),
        ]);

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, $exceptionAdapter);
        } catch (ExceptionalValidationFailedException $e) {
            $violationList = $e->getViolationList();
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

            throw $e;
        }
    }

    public function testValidatorViolationListExceptionMapping(): void
    {
        $message = HandleableMessageStub::create()->withNestedObject(new NestedHandleableMessage());

        $violationList = Validation::createValidator()->validate('123', [$constraint = new Length(max: 2)]);

        $originalException = new ViolationListExampleException($violationList);

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, $originalException);
        } catch (ExceptionalValidationFailedException $e) {
            $violationList = $e->getViolationList();
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

            throw $e;
        }
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

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, $originalException);
        } catch (ExceptionalValidationFailedException $e) {
            $violationList = $e->getViolationList();
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

            throw $e;
        }
    }

    public function testMatchExceptionBySource(): void
    {
        $originalException = null;

        try {
            Email::fromString('non-email');
        } catch (ValidationFailedException $originalException) {
        }

        self::assertNotNull($originalException);

        $message = HandleableMessageStub::create();

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, $originalException);
        } catch (ExceptionalValidationFailedException $e) {
            $violationList = $e->getViolationList();
            self::assertCount(1, $violationList);

            $violation = $violationList[0];
            self::assertInstanceOf(ConstraintViolation::class, $violation);
            self::assertSame('email', $violation->getPropertyPath());

            throw $e;
        }
    }
}
