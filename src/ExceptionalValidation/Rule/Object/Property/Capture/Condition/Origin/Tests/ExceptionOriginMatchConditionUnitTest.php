<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Origin\Tests;

use InvalidArgumentException;
use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Rule\Exception\PropriatedExceptionList;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Email;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Origin\ExceptionOriginMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Origin\ExceptionOriginMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite\CompositeMatchConditionFactory
 *
 * @internal
 */
final class ExceptionOriginMatchConditionUnitTest extends TestCase
{
    /** @var ExceptionMapper<PropriatedExceptionList> */
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalValidationExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMapper<PropriatedExceptionList> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<'.PropriatedExceptionList::class.'>');
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

        $propriatedExceptionList = $this->mapper->map($message, $originalException);

        self::assertNotNull($propriatedExceptionList);
        self::assertCount(1, $propriatedExceptionList);

        [$propriatedException] = $propriatedExceptionList->toArray();

        self::assertSame('email', $propriatedException->getMatchedRule()->getPropertyPath()->join('.'));
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

        $propriatedExceptionList = $this->mapper->map($message, $originalException);

        self::assertNotNull($propriatedExceptionList);
        self::assertCount(1, $propriatedExceptionList);

        [$propriatedException] = $propriatedExceptionList->toArray();

        self::assertSame('uid', $propriatedException->getMatchedRule()->getPropertyPath()->join('.'));
    }
}
