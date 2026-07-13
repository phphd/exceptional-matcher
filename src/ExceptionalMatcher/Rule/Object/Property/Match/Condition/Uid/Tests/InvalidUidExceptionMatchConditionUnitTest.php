<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Uid\Tests;

use InvalidArgumentException;
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Exception\MatchedExceptionList;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Uid\Tests\Stub\MessageWithInvalidUidCondition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;
use Symfony\Component\Uid\Uuid;

use function class_exists;
use function property_exists;

/**
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Uid\InvalidUidExceptionMatchCondition
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Uid\InvalidUidExceptionMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\CompositeMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Bool\FalseCondition
 * @covers \PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension
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

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
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
        $message = new MessageWithInvalidUidCondition(null);

        try {
            Uuid::fromString('just bad');

            self::fail('The exception must be thrown.');
        } catch (InvalidUidException $originalException) {
        }

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testInvalidUidExceptionIsNotCapturedSinceWhenValueIsNotStringable(): void
    {
        $message = new MessageWithInvalidUidCondition([
            'a' => 'very bad',
        ]);

        try {
            Uuid::fromString('very bad');

            self::fail('The exception must be thrown.');
        } catch (InvalidUidException $originalException) {
        }

        $this->expectExceptionMessage('InvalidUidExceptionMatchCondition requires a stringable value, got: array');
        $this->expectException(InvalidArgumentException::class);

        $this->matcher->match($originalException, $message);
    }

    public function testInvalidUidExceptionIsNotCapturedWhenValueIsDifferent(): void
    {
        $message = new MessageWithInvalidUidCondition('way too bad');

        try {
            Uuid::fromString('just bad');

            self::fail('The exception must be thrown.');
        } catch (InvalidUidException $originalException) {
        }

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testInvalidUidExceptionIsCapturedWhenValueMatches(): void
    {
        $message = new MessageWithInvalidUidCondition('bad');

        try {
            Uuid::fromString('bad');

            self::fail('The exception must be thrown.');
        } catch (InvalidUidException $originalException) {
        }

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);
        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame($originalException, $matchedException->getException());
        self::assertSame('uid', $matchedException->getRule()->getPropertyPath()->join('.'));
    }
}
