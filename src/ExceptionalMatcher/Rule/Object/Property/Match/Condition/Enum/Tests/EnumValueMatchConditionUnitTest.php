<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\Tests;

use Exception;
use LogicException;
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Exception\MatchedExceptionList;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Rule\Object\Compiler\PropertyPlanCompilationFailedException;
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
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\EnumValueMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\EnumValueMatchConditionBlueprint
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\CompositeMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Delegating\DelegatingMatchConditionCompiler
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
        $exception = new RuntimeException('oops');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('EnumValueMatchCondition can only be used for ValueError');

        try {
            $this->matcher->match($exception, $message);

            self::fail('PropertyPlanCompilationFailedException should be thrown');
        } catch (PropertyPlanCompilationFailedException $e) {
            self::assertSame('weekDay', $e->getProperty()->getName());

            throw $e->getPrevious();
        }
    }

    public function testThrowsWhenFromClauseIsMissing(): void
    {
        $message = new MissingEnumFromConditionMessage('pend');
        $exception = $this->weekDayEnumError('mon');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('EnumValueMatchCondition requires `from:` to contain a class-string of BackedEnum, got: NULL');

        try {
            $this->matcher->match($exception, $message);

            self::fail('PropertyPlanCompilationFailedException should be thrown');
        } catch (PropertyPlanCompilationFailedException $e) {
            throw $e->getPrevious();
        }
    }

    public function testThrowsWhenFromClassIsNotBackedEnum(): void
    {
        $message = new NonBackedEnumConditionMessage('pend');
        $exception = $this->weekDayEnumError('mon');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf('EnumValueMatchCondition requires `from:` to contain a class-string of BackedEnum, got: %s', var_export(NonBackedStatus::class, true)));

        try {
            $this->matcher->match($exception, $message);

            self::fail('PropertyPlanCompilationFailedException should be thrown');
        } catch (PropertyPlanCompilationFailedException $e) {
            throw $e->getPrevious();
        }
    }

    public function testThrowsWhenFromMethodIsNotEnumFrom(): void
    {
        $message = new InvalidEnumFromMethodConditionMessage('pend');
        $exception = $this->weekDayEnumError('tue');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("EnumValueMatchCondition must specify `from: [WeekDay::class, 'from']`, got: `from: [WeekDay::class, 'tryFrom']`.");

        try {
            $this->matcher->match($exception, $message);

            self::fail('PropertyPlanCompilationFailedException should be thrown');
        } catch (PropertyPlanCompilationFailedException $e) {
            throw $e->getPrevious();
        }
    }

    public function testDoesNotCaptureStringEnumErrorWhenRuleValueIsNull(): void
    {
        $message = new WeekDayConditionMessage(null);
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
        $message = new WeekDayNumberConditionMessage(null);
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
