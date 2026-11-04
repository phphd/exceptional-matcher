<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests;

use InvalidArgumentException;
use LogicException;
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\Exception\ObjectExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\Exception\Tests\Trait\AssertCompilationFailedFor;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\EnumValueMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\ChooseDeliveryMethodCommand;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\Invalid\InvalidEnumFromMethodConditionMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\Invalid\MissingEnumFromConditionMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\Invalid\NonBackedEnumConditionMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\Invalid\NonEnumExceptionClassMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\Issue\DeliveryIssue;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\Method\DeliveryMethod;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\Priority\DeliveryPriority;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\SetDeliveryPriorityCommand;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\MatchedExceptionList;
use PhPhD\ExceptionalMatcher\Tests\Trait\CatchException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use ValueError;

use function sprintf;
use function var_export;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\EnumValueMatchCondition
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\EnumValueMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\EnumValueMatchConditionBlueprint
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\QualifiedMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\PropertyExceptionMappingCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Exception\PropertyExceptionMappingPrintCompilationFailedException
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingPrintCompilationFailedException::getRootException
 *
 * @internal
 */
final class EnumValueMatchConditionUnitTest extends TestCase
{
    use CatchException;
    use AssertCompilationFailedFor;

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

        $compilationException = self::catchException(
            fn () => $this->matcher->match($exception, $message),
            ObjectExceptionMappingPrintCompilationFailedException::class,
        );

        self::assertCompilationFailedFor($compilationException, NonEnumExceptionClassMessage::class, 'deliveryMethod');

        throw $compilationException->getRootException();
    }

    public function testThrowsWhenFromClauseIsMissing(): void
    {
        $message = new MissingEnumFromConditionMessage();
        $exception = $this->deliveryMethodError('lockr');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('EnumValueMatchCondition requires `from:` to contain a class-string of BackedEnum, got: NULL');

        $compilationException = self::catchException(
            fn () => $this->matcher->match($exception, $message),
            ObjectExceptionMappingPrintCompilationFailedException::class,
        );

        self::assertCompilationFailedFor($compilationException, MissingEnumFromConditionMessage::class, 'deliveryMethod');

        throw $compilationException->getRootException();
    }

    public function testThrowsWhenFromClassIsNotBackedEnum(): void
    {
        $message = new NonBackedEnumConditionMessage();
        $exception = $this->deliveryMethodError('lockr');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf(
            'EnumValueMatchCondition requires `from:` to contain a class-string of BackedEnum, got: %s',
            var_export(DeliveryIssue::class, return: true),
        ));

        $compilationException = self::catchException(
            fn () => $this->matcher->match($exception, $message),
            ObjectExceptionMappingPrintCompilationFailedException::class,
        );

        self::assertCompilationFailedFor($compilationException, NonBackedEnumConditionMessage::class, 'issue');

        throw $compilationException->getRootException();
    }

    public function testThrowsWhenFromMethodIsNotEnumFrom(): void
    {
        $message = new InvalidEnumFromMethodConditionMessage();
        $exception = $this->deliveryMethodError('lockr');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("EnumValueMatchCondition must specify `from: [DeliveryMethod::class, 'from']`, got: `from: [DeliveryMethod::class, 'tryFrom']`.");

        $compilationException = self::catchException(
            fn () => $this->matcher->match($exception, $message),
            ObjectExceptionMappingPrintCompilationFailedException::class,
        );

        self::assertCompilationFailedFor($compilationException, InvalidEnumFromMethodConditionMessage::class, 'deliveryMethod');

        throw $compilationException->getRootException();
    }

    public function testDoesNotCaptureStringEnumErrorWhenRuleValueIsNull(): void
    {
        $message = new ChooseDeliveryMethodCommand(deliveryMethod: null);
        $originalException = $this->deliveryMethodError('lockr');

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testDoesNotCaptureStringEnumErrorWhenValueDiffers(): void
    {
        $message = new ChooseDeliveryMethodCommand('courier');
        $originalException = $this->deliveryMethodError('courrier');

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testCapturesStringEnumErrorWhenValueIsSame(): void
    {
        $message = new ChooseDeliveryMethodCommand('lockr');
        $originalException = $this->deliveryMethodError('lockr');

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
    }

    public function testDoesNotCaptureIntEnumErrorWhenRuleValueIsNull(): void
    {
        $message = new SetDeliveryPriorityCommand(priority: null);
        $originalException = $this->deliveryPriorityError(4);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testDoesNotCaptureIntEnumErrorWhenValueDiffers(): void
    {
        $message = new SetDeliveryPriorityCommand(3);
        $originalException = $this->deliveryPriorityError(4);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
    }

    public function testCapturesIntEnumErrorWhenValueIsSame(): void
    {
        $message = new SetDeliveryPriorityCommand(4);
        $originalException = $this->deliveryPriorityError(4);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
    }

    private function deliveryMethodError(string $value): ValueError
    {
        return self::catchException(static fn () => DeliveryMethod::from($value), ValueError::class);
    }

    private function deliveryPriorityError(int $value): ValueError
    {
        return self::catchException(static fn () => DeliveryPriority::from($value), ValueError::class);
    }
}
