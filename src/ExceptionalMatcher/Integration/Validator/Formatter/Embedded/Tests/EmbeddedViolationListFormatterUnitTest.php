<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\Tests\Stub\ViolationsEmbeddedExampleException;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\NestedHandleableMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

use function array_flip;
use function array_intersect_key;

/**
 * @covers \PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\ViolationsEmbeddedExceptionFormatter
 *
 * @internal
 */
final class EmbeddedViolationListFormatterUnitTest extends TestCase
{
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

    public function testViolationsEmbeddedExceptionMapping(): void
    {
        $violationList = Validation::createValidator()->validate('123', [$constraint = new Length(max: 2)]);

        $message = HandleableMessageStub::create()->withNestedObject(new NestedHandleableMessage());
        $originalException = new ViolationsEmbeddedExampleException($violationList);

        $violationList = $this->matcher->match($originalException, $message);

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
