<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\ViolationList\Tests;

use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\ViolationList\Tests\Stub\ViolationListExampleException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\NestedHandleableMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

use function array_flip;
use function array_intersect_key;

/**
 * @covers \PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\ViolationList\ViolationListExceptionFormatter
 *
 * @internal
 */
final class ViolationListExceptionFormatterUnitTest extends TestCase
{
    /** @var ExceptionMapper<ConstraintViolationListInterface> */
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalValidationExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMapper<ConstraintViolationListInterface> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>');
        $this->mapper = $mapper;
    }

    public function testValidatorViolationListExceptionMapping(): void
    {
        $message = HandleableMessageStub::create()->withNestedObject(new NestedHandleableMessage());

        $violationList = Validation::createValidator()->validate('123', [$constraint = new Length(max: 2)]);

        $originalException = new ViolationListExampleException($violationList);

        $violationList = $this->mapper->map($message, $originalException);

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
}
