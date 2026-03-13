<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Uid\Tests;

use InvalidArgumentException;
use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Rule\Exception\MatchedExceptionList;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Uid\Tests\Stub\MessageWithInvalidUidCondition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;

use Symfony\Component\Uid\Uuid;

use function class_exists;
use function property_exists;

/**
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Uid\InvalidUidExceptionMatchCondition
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Uid\InvalidUidExceptionMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite\CompositeMatchConditionFactory
 * @covers \PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Bool\FalseCondition
 * @covers \PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension
 *
 * @internal
 */
final class InvalidUidExceptionMatchConditionUnitTest extends TestCase
{
    /** @var ExceptionMapper<MatchedExceptionList> */
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists(InvalidUidException::class) || !property_exists(InvalidUidException::class, 'invalidValue')) {
            self::markTestSkipped('Installed version of Symfony Uid component does not fit for matching.');
        }

        $container = (new PhdExceptionalValidationExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMapper<MatchedExceptionList> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<'.MatchedExceptionList::class.'>');
        $this->mapper = $mapper;
    }

    public function testInvalidUidExceptionIsNotCapturedWhenValueIsNull(): void
    {
        $message = new MessageWithInvalidUidCondition(null);

        try {
            Uuid::fromString('just bad');

            self::fail('Exception must be thrown.');
        } catch (InvalidUidException $originalException) {
        }

        $matchedExceptionList = $this->mapper->map($message, $originalException);

        self::assertNull($matchedExceptionList);
    }

    public function testInvalidUidExceptionIsNotCapturedSinceWhenValueIsNotStringable(): void
    {
        $message = new MessageWithInvalidUidCondition([
            'a' => 'very bad',
        ]);

        try {
            Uuid::fromString('very bad');

            self::fail('Exception must be thrown.');
        } catch (InvalidUidException $originalException) {
        }

        $this->expectExceptionMessage('InvalidUidExceptionMatchCondition requires a stringable value, got: array');
        $this->expectException(InvalidArgumentException::class);

        $this->mapper->map($message, $originalException);
    }

    public function testInvalidUidExceptionIsNotCapturedWhenValueIsDifferent(): void
    {
        $message = new MessageWithInvalidUidCondition('way too bad');

        try {
            Uuid::fromString('just bad');

            self::fail('Exception must be thrown.');
        } catch (InvalidUidException $originalException) {
        }

        $matchedExceptionList = $this->mapper->map($message, $originalException);

        self::assertNull($matchedExceptionList);
    }

    public function testInvalidUidExceptionIsCapturedWhenValueMatches(): void
    {
        $message = new MessageWithInvalidUidCondition('bad');

        try {
            Uuid::fromString('bad');

            self::fail('Exception must be thrown.');
        } catch (InvalidUidException $originalException) {
        }

        $matchedExceptionList = $this->mapper->map($message, $originalException);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);
        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame($originalException, $matchedException->getException());
        self::assertSame('uid', $matchedException->getRule()->getPropertyPath()->join('.'));
    }
}
