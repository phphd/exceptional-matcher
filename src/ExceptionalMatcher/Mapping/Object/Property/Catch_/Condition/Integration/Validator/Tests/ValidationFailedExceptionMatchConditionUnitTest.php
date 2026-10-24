<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Validator\Tests;

use InvalidArgumentException;
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Exception\ObjectExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Exception\Tests\Trait\RootCompilationException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Validator\Tests\Stub\Invalid\NonValidationFailedExceptionClassMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Validator\Tests\Stub\MessageWithValidatedValueCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Validator\ValidationFailedExceptionMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Tests\Trait\CatchesExceptions;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validation;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Validator\ValidationFailedExceptionMatchCondition
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Validator\ValidationFailedExceptionMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\ViolationsEmbeddedExceptionFormatter
 * @covers \PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension
 *
 * @internal
 */
final class ValidationFailedExceptionMatchConditionUnitTest extends TestCase
{
    use CatchesExceptions;
    use RootCompilationException;

    /** @var ExceptionMatcher<ConstraintViolationListInterface> */
    private ExceptionMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMatcher<ConstraintViolationListInterface> $matcher */
        $matcher = $container->get(ExceptionMatcher::class.'<'.ConstraintViolationListInterface::class.'>');
        $this->matcher = $matcher;
    }

    public function testThrowsWhenMatchConditionUsesNonValidationFailedExceptionClass(): void
    {
        $message = new NonValidationFailedExceptionClassMessage();
        $exception = new RuntimeException();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ValidationFailedExceptionMatchConditionCompiler::TYPE_GUARANTEE_MESSAGE);

        try {
            $this->matcher->match($exception, $message);
        } catch (ObjectExceptionMappingPlanCompilationFailedException $e) {
            throw self::rootCompilationException($e, NonValidationFailedExceptionClassMessage::class, 'value');
        }

        self::fail('The exception must be thrown.');
    }

    public function testValidationFailedExceptionCanBeCaptured(): void
    {
        $message = new MessageWithValidatedValueCondition();

        $validation = Validation::createCallable($constraint = new Length(min: 11));

        $validationFailedException = self::catchException(
            static fn (): mixed => $validation('matched!'),
            ValidationFailedException::class,
        );

        $violationList = $this->matcher->match($validationFailedException, $message);

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
