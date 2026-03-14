<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Origin\Tests;

use InvalidArgumentException;
use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Matcher\ExceptionMatcher;
use PhPhD\ExceptionalValidation\Rule\Exception\MatchedExceptionList;
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
    /** @var ExceptionMatcher<MatchedExceptionList> */
    private ExceptionMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalValidationExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMatcher<MatchedExceptionList> $matcher */
        $matcher = $container->get(ExceptionMatcher::class.'<'.MatchedExceptionList::class.'>');
        $this->matcher = $matcher;
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

        $matchedExceptionList = $this->matcher->map($message, $originalException);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('email', $matchedException->getRule()->getPropertyPath()->join('.'));
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

        $matchedExceptionList = $this->matcher->map($message, $originalException);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('uid', $matchedException->getRule()->getPropertyPath()->join('.'));
    }
}
