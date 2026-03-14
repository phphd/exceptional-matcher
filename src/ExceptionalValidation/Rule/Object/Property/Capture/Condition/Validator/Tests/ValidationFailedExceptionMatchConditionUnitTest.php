<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\Tests;

use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Matcher\ExceptionMatcher;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validation;

/**
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Matcher\Validator\Formatter\Validator\ValidationFailedExceptionFormatter
 * @covers \PhPhD\ExceptionalValidation\Matcher\Validator\Formatter\Validator\ValidationFailedExceptionAdapter
 * @covers \PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension
 *
 * @internal
 */
final class ValidationFailedExceptionMatchConditionUnitTest extends TestCase
{
    /** @var ExceptionMatcher<ConstraintViolationListInterface> */
    private ExceptionMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalValidationExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMatcher<ConstraintViolationListInterface> $matcher */
        $matcher = $container->get(ExceptionMatcher::class.'<'.ConstraintViolationListInterface::class.'>');
        $this->matcher = $matcher;
    }

    public function testValidationFailedExceptionCanBeCaptured(): void
    {
        $validation = Validation::createCallable($constraint = new Length(min: 11));

        try {
            $validation('matched!');

            self::fail('The exception must be thrown.');
        } catch (ValidationFailedException $originalException) {
        }

        $message = HandleableMessageStub::create();

        $violationList = $this->matcher->match($originalException, $message);

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
