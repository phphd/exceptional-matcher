<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\Tests;

use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\CompositeExceptionUnwrapper;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionToolkit\Unwrapper\PassThroughExceptionUnwrapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validation;

/**
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Validator\ValidationFailedExceptionFormatter
 * @covers \PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Validator\ValidationFailedExceptionAdapter
 *
 * @internal
 */
final class ValidationFailedExceptionMatchConditionUnitTest extends TestCase
{
    /** @var ExceptionMapper<ConstraintViolationListInterface> */
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $container = PhdExceptionalValidationExtension::getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ], true);

        $exceptionUnwrapper = new CompositeExceptionUnwrapper(new PassThroughExceptionUnwrapper());
        $container->set('phd_exception_toolkit.exception_unwrapper', $exceptionUnwrapper);

        $container->compile();

        /** @var ExceptionMapper<ConstraintViolationListInterface> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>');
        $this->mapper = $mapper;
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

        $violationList = $this->mapper->map($message, $originalException);

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
}
