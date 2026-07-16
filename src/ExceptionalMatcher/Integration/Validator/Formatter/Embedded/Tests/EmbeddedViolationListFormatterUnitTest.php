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
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

use function array_diff_key;
use function array_flip;
use function sprintf;
use function strtr;

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

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')
            ->willReturnCallback(static fn (
                string $id,
                array $params,
                string $domain,
            ): string => sprintf('re-translated [%s]: %s ', $domain, strtr($id, $params)))
        ;
        $container->set('translator', $translator);
        $container->setParameter('validator.translation_domain', 'domain');

        $container->compile();

        /** @var ExceptionMatcher<ConstraintViolationListInterface> $matcher */
        $matcher = $container->get(ExceptionMatcher::class.'<'.ConstraintViolationListInterface::class.'>');
        $this->matcher = $matcher;
    }

    public function testViolationsEmbeddedExceptionProvidesViolationsList(): void
    {
        $message = HandleableMessageStub::create()->withNestedObject(new NestedHandleableMessage());

        $violationList = Validation::createValidator()->validate('123', [$constraint = new Length(max: 2)]);
        $originalException = new ViolationsEmbeddedExampleException($violationList);

        $violation = $this->matchOne($originalException, $message);

        // Pre-translated message is kept as-is: a ViolationsEmbeddedException is not re-translated.
        self::assertSame(
            'This value is too long. It should have 2 characters or less.',
            $violation->getMessage(),
        );
        self::assertSame(
            'This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.',
            $violation->getMessageTemplate(),
        );
        self::assertSame([
            '{{ value }}' => '"123"',
            '{{ limit }}' => '2',
            '{{ value_length }}' => '3',
            // older versions have no '{{ max }}' param
        ], array_diff_key($violation->getParameters(), array_flip(['{{ max }}'])));

        self::assertSame(2, $violation->getPlural());
        self::assertSame($message, $violation->getRoot());
        self::assertSame('nestedObject.violationListCapturedProperty', $violation->getPropertyPath());
        self::assertSame('123', $violation->getInvalidValue());
        self::assertSame(Length::TOO_LONG_ERROR, $violation->getCode());
        self::assertSame($constraint, $violation->getConstraint());
        self::assertNull($violation->getCause());
    }

    /**
     * Validation::createCallable() eagerly translates with the server-default locale,
     * so the formatter must re-translate to honour the request's Accept-Language.
     */
    public function testValidationFailedExceptionMessageIsRetranslated(): void
    {
        $message = HandleableMessageStub::create();

        try {
            Validation::createCallable(new Length(max: 3))('matched!');

            self::fail('The exception must be thrown.');
        } catch (ValidationFailedException $originalException) {
        }

        $violation = $this->matchOne($originalException, $message);

        // Message is re-translated, as it's from Validation::class exception
        self::assertSame(
            're-translated [domain]: This value is too long. It should have 3 character or less.|This value is too long. It should have 3 characters or less. ',
            $violation->getMessage(),
        );
        self::assertSame('matchedProperty', $violation->getPropertyPath());
    }

    public function testManuallyThrownValidationFailedExceptionIsNotRetranslated(): void
    {
        $message = HandleableMessageStub::create();

        $violations = Validation::createValidator()->validate('matched!', [new Length(max: 3)]);
        // Not thrown from Validation::createCallable(), so it's not re-translated
        $originalException = new ValidationFailedException('matched!', $violations);

        $violation = $this->matchOne($originalException, $message);

        // The message is kept "as is":
        self::assertSame(
            'This value is too long. It should have 3 characters or less.',
            $violation->getMessage(),
        );
        self::assertSame('matchedProperty', $violation->getPropertyPath());
    }

    private function matchOne(Throwable $originalException, HandleableMessageStub $message): ConstraintViolation
    {
        $violationList = $this->matcher->match($originalException, $message);

        self::assertNotNull($violationList);
        self::assertCount(1, $violationList);

        $violation = $violationList[0];
        self::assertInstanceOf(ConstraintViolation::class, $violation);

        return $violation;
    }
}
