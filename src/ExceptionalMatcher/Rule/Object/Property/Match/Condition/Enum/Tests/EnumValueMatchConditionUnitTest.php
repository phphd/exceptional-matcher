<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\Tests;

use LogicException;
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Exception\MatchedExceptionList;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\Tests\Stub\Invalid\InvalidEnumFromMethodConditionMessage;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\Tests\Stub\Invalid\MissingEnumFromConditionMessage;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\Tests\Stub\Invalid\NonEnumExceptionClassConditionMessage;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\Tests\Stub\Invalid\NotBacked\NonBackedEnumConditionMessage;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\Tests\Stub\Invalid\NotBacked\NonBackedStatus;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\Tests\Stub\WeekDay\WeekDay;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\Tests\Stub\WeekDay\WeekDayConditionMessage;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\Tests\Stub\WeekDayNumber\WeekDayNumber;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\Tests\Stub\WeekDayNumber\WeekDayNumberConditionMessage;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use ValueError;

use function sprintf;
use function var_export;

/**
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\EnumValueMatchCondition
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\EnumValueMatchConditionFactory
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\CompositeMatchConditionFactory
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Delegating\DelegatingMatchConditionFactory
 *
 * @internal
 */
final class EnumValueMatchConditionUnitTest extends TestCase
{
    /** @var ExceptionMatcher<MatchedExceptionList> */
    private ExceptionMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMatcher<MatchedExceptionList> $matcher */
        $matcher = $container->get(ExceptionMatcher::class.'<'.MatchedExceptionList::class.'>');
        $this->matcher = $matcher;
    }

    public function testThrowsWhenMatchConditionIsForNonEnumExceptionClass(): void
    {
        $message = new NonEnumExceptionClassConditionMessage('pend');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('EnumValueMatchCondition can only be used for ValueError');

        $this->matcher->match(new RuntimeException('oops'), $message);
    }

    public function testThrowsWhenFromClauseIsMissing(): void
    {
        $message = new MissingEnumFromConditionMessage('pend');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('EnumValueMatchCondition requires `from:` to contain a class-string of BackedEnum, got: NULL');

        $this->matcher->match($this->weekDayEnumError('mon'), $message);
    }

    public function testThrowsWhenFromClassIsNotBackedEnum(): void
    {
        $message = new NonBackedEnumConditionMessage('pend');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf('EnumValueMatchCondition requires `from:` to contain a class-string of BackedEnum, got: %s', var_export(NonBackedStatus::class, true)));

        $this->matcher->match($this->weekDayEnumError('mon'), $message);
    }

    public function testThrowsWhenFromMethodIsNotEnumFrom(): void
    {
        $message = new InvalidEnumFromMethodConditionMessage('pend');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("EnumValueMatchCondition must specify `from: [WeekDay::class, 'from']`, got: `from: [WeekDay::class, 'tryFrom']`.");

        $this->matcher->match($this->weekDayEnumError('tue'), $message);
    }

    public function testDoesNotCaptureStringEnumErrorWhenRuleValueIsNull(): void
    {
        $originalException = $this->weekDayEnumError('');
        $message = new WeekDayConditionMessage(null);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testDoesNotCaptureStringEnumErrorWhenValueDiffers(): void
    {
        $originalException = $this->weekDayEnumError('mon');
        $message = new WeekDayConditionMessage('monday');

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testCapturesStringEnumErrorWhenValueIsSame(): void
    {
        $originalException = $this->weekDayEnumError('tue');
        $message = new WeekDayConditionMessage('tue');

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
    }

    public function testDoesNotCaptureIntEnumErrorWhenRuleValueIsNull(): void
    {
        $originalException = $this->weekDayNumberEnumError(0);
        $message = new WeekDayNumberConditionMessage(null);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testDoesNotCaptureIntEnumErrorWhenValueDiffers(): void
    {
        $originalException = $this->weekDayNumberEnumError(8);
        $message = new WeekDayNumberConditionMessage(7);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testCapturesIntEnumErrorWhenValueIsSame(): void
    {
        $originalException = $this->weekDayNumberEnumError(8);
        $message = new WeekDayNumberConditionMessage(8);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
    }

    private function weekDayEnumError(string $value): ValueError
    {
        try {
            WeekDay::from($value);

            self::fail('The exception must be thrown.');
        } catch (ValueError $exception) {
        }

        return $exception;
    }

    private function weekDayNumberEnumError(int $value): ValueError
    {
        try {
            WeekDayNumber::from($value);

            self::fail('The exception must be thrown.');
        } catch (ValueError $exception) {
        }

        return $exception;
    }
}
