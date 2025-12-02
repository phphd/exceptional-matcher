<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Origin;

use InvalidArgumentException;
use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Rule\Exception\CapturedException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Email;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

/**
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Origin\ExceptionOriginMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Origin\ExceptionOriginMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite\CaptureMatchConditionFactory
 *
 * @internal
 */
final class ExceptionOriginMatchConditionUnitTest extends TestCase
{
    /** @var ExceptionMapper<non-empty-list<CapturedException<Throwable>>> */
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalValidationExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMapper<non-empty-list<CapturedException<Throwable>>> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<non-empty-list<'.CapturedException::class.'<Throwable>>>');
        $this->mapper = $mapper;
    }

    public function testMatchExceptionByOriginClass(): void
    {
        $originalException = null;

        try {
            /** @psalm-suppress UnusedMethodCall */
            Email::fromString('non-email')->getEmail(); // @phpstan-ignore method.resultUnused
        } catch (ValidationFailedException $originalException) {
        }

        self::assertNotNull($originalException);

        $message = HandleableMessageStub::create();

        $capturedExceptions = $this->mapper->map($message, $originalException);

        self::assertNotNull($capturedExceptions);
        self::assertCount(1, $capturedExceptions);

        $capturedException = $capturedExceptions[0];
        self::assertSame('email', $capturedException->getMatchedRule()->getPropertyPath()->join('.'));
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

        $capturedExceptions = $this->mapper->map($message, $originalException);

        self::assertNotNull($capturedExceptions);
        self::assertCount(1, $capturedExceptions);

        $capturedException = $capturedExceptions[0];
        self::assertSame('uid', $capturedException->getMatchedRule()->getPropertyPath()->join('.'));
    }
}
