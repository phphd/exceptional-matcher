<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\Tests;

use InvalidArgumentException;
use LogicException;
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Exception\ObjectExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Exception\Tests\Trait\RootCompilationException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\EnumValueMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\Tests\Stub\Invalid\InvalidEnumFromMethodConditionMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\Tests\Stub\Invalid\MissingEnumFromConditionMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\Tests\Stub\Invalid\NonEnumExceptionClassMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\Tests\Stub\Invalid\NotBacked\NonBackedEnumConditionMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\Tests\Stub\Invalid\NotBacked\NonBackedStatus;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\Tests\Stub\WeekDay\WeekDay;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\Tests\Stub\WeekDay\WeekDayConditionMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\Tests\Stub\WeekDayNumber\WeekDayNumber;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\Tests\Stub\WeekDayNumber\WeekDayNumberConditionMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\MatchedExceptionList;
use PhPhD\ExceptionalMatcher\Tests\Trait\CatchesExceptions;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use ValueError;

use function sprintf;
use function var_export;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\EnumValueMatchCondition
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\EnumValueMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\EnumValueMatchConditionPlan
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Delegating\DelegatingMatchConditionCompiler
 *
 * @internal
 */
final class EnumValueMatchConditionUnitTest extends TestCase
{
    use CatchesExceptions;
    use RootCompilationException;

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

    public function testThrowsWhenMatchConditionUsesNonEnumExceptionClass(): void
    {
        $message = new NonEnumExceptionClassMessage();
        $exception = new RuntimeException();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(EnumValueMatchConditionCompiler::TYPE_GUARANTEE_MESSAGE);

        try {
            $this->matcher->match($exception, $message);
        } catch (ObjectExceptionMappingPlanCompilationFailedException $e) {
            throw self::rootCompilationException($e, NonEnumExceptionClassMessage::class, 'weekDay');
        }

        self::fail('The exception must be thrown.');
    }

    public function testThrowsWhenFromClauseIsMissing(): void
    {
        $message = new MissingEnumFromConditionMessage();
        $exception = $this->weekDayEnumError('mon');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('EnumValueMatchCondition requires `from:` to contain a class-string of BackedEnum, got: NULL');

        try {
            $this->matcher->match($exception, $message);
        } catch (ObjectExceptionMappingPlanCompilationFailedException $e) {
            throw self::rootCompilationException($e, MissingEnumFromConditionMessage::class, 'weekDay');
        }

        self::fail('The exception must be thrown.');
    }

    public function testThrowsWhenFromClassIsNotBackedEnum(): void
    {
        $message = new NonBackedEnumConditionMessage();
        $exception = $this->weekDayEnumError('mon');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf(
            'EnumValueMatchCondition requires `from:` to contain a class-string of BackedEnum, got: %s',
            var_export(NonBackedStatus::class, return: true),
        ));

        try {
            $this->matcher->match($exception, $message);
        } catch (ObjectExceptionMappingPlanCompilationFailedException $e) {
            throw self::rootCompilationException($e, NonBackedEnumConditionMessage::class, 'status');
        }

        self::fail('The exception must be thrown.');
    }

    public function testThrowsWhenFromMethodIsNotEnumFrom(): void
    {
        $message = new InvalidEnumFromMethodConditionMessage();
        $exception = $this->weekDayEnumError('tue');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("EnumValueMatchCondition must specify `from: [WeekDay::class, 'from']`, got: `from: [WeekDay::class, 'tryFrom']`.");

        try {
            $this->matcher->match($exception, $message);
        } catch (ObjectExceptionMappingPlanCompilationFailedException $e) {
            throw self::rootCompilationException($e, InvalidEnumFromMethodConditionMessage::class, 'weekDay');
        }

        self::fail('The exception must be thrown.');
    }

    public function testDoesNotCaptureStringEnumErrorWhenRuleValueIsNull(): void
    {
        $message = new WeekDayConditionMessage(weekDay: null);
        $originalException = $this->weekDayEnumError('');

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testDoesNotCaptureStringEnumErrorWhenValueDiffers(): void
    {
        $message = new WeekDayConditionMessage('monday');
        $originalException = $this->weekDayEnumError('mon');

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testCapturesStringEnumErrorWhenValueIsSame(): void
    {
        $message = new WeekDayConditionMessage('tue');
        $originalException = $this->weekDayEnumError('tue');

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
    }

    public function testDoesNotCaptureIntEnumErrorWhenRuleValueIsNull(): void
    {
        $message = new WeekDayNumberConditionMessage(weekDayNumber: null);
        $originalException = $this->weekDayNumberEnumError(0);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testDoesNotCaptureIntEnumErrorWhenValueDiffers(): void
    {
        $message = new WeekDayNumberConditionMessage(7);
        $originalException = $this->weekDayNumberEnumError(8);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testCapturesIntEnumErrorWhenValueIsSame(): void
    {
        $message = new WeekDayNumberConditionMessage(8);
        $originalException = $this->weekDayNumberEnumError(8);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
    }

    private function weekDayEnumError(string $value): ValueError
    {
        return self::catchException(static fn () => WeekDay::from($value), ValueError::class);
    }

    private function weekDayNumberEnumError(int $value): ValueError
    {
        return self::catchException(static fn () => WeekDayNumber::from($value), ValueError::class);
    }
}
