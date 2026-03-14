<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Uid\Tests;

use InvalidArgumentException;
use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Matcher\ExceptionMatcher;
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
    /** @var ExceptionMatcher<MatchedExceptionList> */
    private ExceptionMatcher $matcher;

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

        /** @var ExceptionMatcher<MatchedExceptionList> $matcher */
        $matcher = $container->get(ExceptionMatcher::class.'<'.MatchedExceptionList::class.'>');
        $this->matcher = $matcher;
    }

    public function testInvalidUidExceptionIsNotCapturedWhenValueIsNull(): void
    {
        try {
            Uuid::fromString('just bad');

            self::fail('The exception must be thrown.');
        } catch (InvalidUidException $originalException) {
        }

        $message = new MessageWithInvalidUidCondition(null);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testInvalidUidExceptionIsNotCapturedSinceWhenValueIsNotStringable(): void
    {
        try {
            Uuid::fromString('very bad');

            self::fail('The exception must be thrown.');
        } catch (InvalidUidException $originalException) {
        }

        $this->expectExceptionMessage('InvalidUidExceptionMatchCondition requires a stringable value, got: array');
        $this->expectException(InvalidArgumentException::class);

        $message = new MessageWithInvalidUidCondition([
            'a' => 'very bad',
        ]);

        $this->matcher->match($originalException, $message);
    }

    public function testInvalidUidExceptionIsNotCapturedWhenValueIsDifferent(): void
    {
        try {
            Uuid::fromString('just bad');

            self::fail('The exception must be thrown.');
        } catch (InvalidUidException $originalException) {
        }

        $message = new MessageWithInvalidUidCondition('way too bad');

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testInvalidUidExceptionIsCapturedWhenValueMatches(): void
    {
        try {
            Uuid::fromString('bad');

            self::fail('The exception must be thrown.');
        } catch (InvalidUidException $originalException) {
        }

        $message = new MessageWithInvalidUidCondition('bad');

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);
        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame($originalException, $matchedException->getException());
        self::assertSame('uid', $matchedException->getRule()->getPropertyPath()->join('.'));
    }
}
