<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests;

use ArrayIterator;
use LogicException;
use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\CompositeRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\ObjectRulesAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\PropertyRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\Rules\PropertyCaptureRulesAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\Rules\PropertyNestedValidObjectRuleAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\Rules\PropertyRulesAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Formatter\ExceptionalViolationFormatter;
use PhPhD\ExceptionalValidation\Formatter\ExceptionalViolationsListFormatter;
use PhPhD\ExceptionalValidation\Handler\Exception\ExceptionalValidationFailedException;
use PhPhD\ExceptionalValidation\Handler\ExceptionalHandler;
use PhPhD\ExceptionalValidation\Tests\Stub\ConditionalMessage;
use PhPhD\ExceptionalValidation\Tests\Stub\Exception\ConditionallyCapturedException;
use PhPhD\ExceptionalValidation\Tests\Stub\Exception\NestedPropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Stub\Exception\ObjectPropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Stub\Exception\PropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Stub\Exception\StaticPropertyCapturedException;
use PhPhD\ExceptionalValidation\Tests\Stub\HandleableMessageStub;
use PhPhD\ExceptionalValidation\Tests\Stub\NestedHandleableMessage;
use PhPhD\ExceptionalValidation\Tests\Stub\NotHandleableMessageStub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @covers \PhPhD\ExceptionalValidation
 * @covers \PhPhD\ExceptionalValidation\Capture
 * @covers \PhPhD\ExceptionalValidation\Handler\ExceptionalHandler
 * @covers \PhPhD\ExceptionalValidation\Handler\Exception\ExceptionalValidationFailedException
 * @covers \PhPhD\ExceptionalValidation\Formatter\ExceptionalViolationsListFormatter
 * @covers \PhPhD\ExceptionalValidation\Formatter\ExceptionalViolationFormatter
 * @covers \PhPhD\ExceptionalValidation\Model\Rule\ObjectRuleSet
 * @covers \PhPhD\ExceptionalValidation\Model\Rule\PropertyRuleSet
 * @covers \PhPhD\ExceptionalValidation\Model\Rule\CompositeRuleSet
 * @covers \PhPhD\ExceptionalValidation\Model\Rule\LazyRuleSet
 * @covers \PhPhD\ExceptionalValidation\Model\Rule\CaptureExceptionRule
 * @covers \PhPhD\ExceptionalValidation\Model\Condition\MatchByExceptionClassCondition
 * @covers \PhPhD\ExceptionalValidation\Model\Condition\MatchWithClosureCondition
 * @covers \PhPhD\ExceptionalValidation\Model\Condition\CompositeMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Model\ValueObject\CaughtException
 * @covers \PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath
 * @covers \PhPhD\ExceptionalValidation\Assembler\CompositeRuleSetAssembler
 * @covers \PhPhD\ExceptionalValidation\Assembler\Object\ObjectRuleSetAssembler
 * @covers \PhPhD\ExceptionalValidation\Assembler\Object\Rules\ObjectRulesAssemblerEnvelope
 * @covers \PhPhD\ExceptionalValidation\Assembler\Object\Rules\ObjectRulesAssembler
 * @covers \PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\PropertyRuleSetAssemblerEnvelope
 * @covers \PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\PropertyRuleSetAssembler
 * @covers \PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\Rules\PropertyRulesAssemblerEnvelope
 * @covers \PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\Rules\PropertyCaptureRulesAssembler
 * @covers \PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\Rules\PropertyNestedValidObjectRuleAssembler
 *
 * @internal
 */
final class ExceptionalValidationTest extends TestCase
{
    private ExceptionalHandler $exceptionHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')
            ->willReturnMap([
                ['oops', [], 'domain', null, 'oops - translated'],
                ['object.oops', [], 'domain', null, 'object.oops - translated'],
                ['nested.message', [], 'domain', null, 'nested.message - translated'],
            ]);

        /** @var ArrayIterator<array-key,CaptureRuleSetAssembler<PropertyRulesAssemblerEnvelope>> $captureListAssemblers */
        $captureListAssemblers = new ArrayIterator();
        $propertyRulesAssembler = new CompositeRuleSetAssembler($captureListAssemblers);
        $propertyRuleSetAssembler = new PropertyRuleSetAssembler($propertyRulesAssembler);

        $objectRulesAssembler = new ObjectRulesAssembler($propertyRuleSetAssembler);
        $objectRuleSetAssembler = new ObjectRuleSetAssembler($objectRulesAssembler);

        $captureListAssemblers->append(new PropertyCaptureRulesAssembler());
        $captureListAssemblers->append(new PropertyNestedValidObjectRuleAssembler($objectRuleSetAssembler));

        $formatter = new ExceptionalViolationFormatter($translator, 'domain');
        $listFormatter = new ExceptionalViolationsListFormatter($formatter);
        $this->exceptionHandler = new ExceptionalHandler($objectRuleSetAssembler, $listFormatter);
    }

    public function testDoesNotCaptureExceptionForMessageNotHavingExceptionalValidationAttribute(): void
    {
        $message = new NotHandleableMessageStub(123);

        $this->expectExceptionObject($exception = new PropertyCapturableException());

        $this->exceptionHandler->capture($message, $exception);
    }

    public function testCapturesExceptionMappedToProperty(): void
    {
        $message = HandleableMessageStub::createEmpty();
        $rootException = new PropertyCapturableException();

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, $rootException);
        } catch (ExceptionalValidationFailedException $e) {
            self::assertSame(
                'Message of type "PhPhD\ExceptionalValidation\Tests\Stub\HandleableMessageStub" has failed exceptional validation.',
                $e->getMessage(),
            );
            self::assertSame($rootException, $e->getPrevious());
            self::assertSame($message, $e->getViolatingMessage());

            $violationList = $e->getViolations();
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
        $message = HandleableMessageStub::createWithMessageText('invalid text value');

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, new LogicException());
        } catch (ExceptionalValidationFailedException $e) {
            /** @var ConstraintViolationInterface $violation */
            [$violation] = $e->getViolations();

            self::assertSame('invalid text value', $violation->getInvalidValue());

            throw $e;
        }
    }

    public function testCollectsObjectInvalidValue(): void
    {
        $message = HandleableMessageStub::createWithObjectProperty($object = new stdClass());

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, new ObjectPropertyCapturableException());
        } catch (ExceptionalValidationFailedException $e) {
            /** @var ConstraintViolationInterface $violation */
            [$violation] = $e->getViolations();

            self::assertSame('object.oops - translated', $violation->getMessage());
            self::assertSame('object.oops', $violation->getMessageTemplate());
            self::assertSame($object, $violation->getInvalidValue());

            throw $e;
        }
    }

    public function testCapturesExceptionsMappedToStaticProperties(): void
    {
        $message = HandleableMessageStub::createEmpty();

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, new StaticPropertyCapturedException());
        } catch (ExceptionalValidationFailedException $e) {
            /** @var ConstraintViolationInterface $violation */
            [$violation] = $e->getViolations();

            self::assertSame('staticProperty', $violation->getPropertyPath());
            self::assertSame('foo', $violation->getInvalidValue());

            throw $e;
        }
    }

    public function testDoesNotCaptureNestedObjectWithoutValidPropertyAttribute(): void
    {
        $message = HandleableMessageStub::createWithOrdinaryObject(new NestedHandleableMessage());

        $this->expectExceptionObject($exception = new NestedPropertyCapturableException());

        $this->exceptionHandler->capture($message, $exception);
    }

    public function testDoesNotCaptureNotInitializedValidNestedObjectProperty(): void
    {
        $message = HandleableMessageStub::createEmpty();

        $this->expectExceptionObject($exception = new NestedPropertyCapturableException());

        $this->exceptionHandler->capture($message, $exception);
    }

    public function testCapturesNestedObjectPropertyException(): void
    {
        $message = HandleableMessageStub::createWithNestedObject(new NestedHandleableMessage());

        $rootException = new NestedPropertyCapturableException();

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, $rootException);
        } catch (ExceptionalValidationFailedException $e) {
            self::assertSame($rootException, $e->getPrevious());

            $violations = $e->getViolations();
            self::assertCount(1, $violations);

            /** @var ConstraintViolationInterface $violation */
            $violation = $violations[0];
            self::assertSame('nested.message - translated', $violation->getMessage());
            self::assertSame('nested.message', $violation->getMessageTemplate());
            self::assertSame('nestedObject.nestedProperty', $violation->getPropertyPath());
            self::assertNull($violation->getInvalidValue());

            throw $e;
        }
    }

    public function testCapturesExceptionWithGivenCondition(): void
    {
        $message = HandleableMessageStub::createWithConditionalMessage(11, 41);

        $rootException = new ConditionallyCapturedException(41);

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->exceptionHandler->capture($message, $rootException);
        } catch (ExceptionalValidationFailedException $e) {
            self::assertSame($rootException, $e->getPrevious());

            $violations = $e->getViolations();
            self::assertCount(1, $violations);

            /** @var ConstraintViolationInterface $violation */
            $violation = $violations[0];
            self::assertSame('nestedObject.conditionalMessage.secondProperty', $violation->getPropertyPath());
            self::assertSame(41, $violation->getInvalidValue());

            throw $e;
        }
    }

    public function testDoesntCaptureAnyExceptionWhenConditionIsNotMet(): void
    {
        $message = HandleableMessageStub::createWithConditionalMessage(11, 41);

        $rootException = new ConditionallyCapturedException(12);

        $this->expectExceptionObject($rootException);

        $this->exceptionHandler->capture($message, $rootException);
    }
}
