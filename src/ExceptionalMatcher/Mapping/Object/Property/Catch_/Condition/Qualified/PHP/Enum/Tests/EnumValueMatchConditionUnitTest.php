<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\ChooseDeliveryMethodCommand;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\Method\DeliveryMethod;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\Priority\DeliveryPriority;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\SetDeliveryPriorityCommand;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\MatchedExceptionList;
use PhPhD\ExceptionalMatcher\Tests\Trait\CatchException;
use PHPUnit\Framework\TestCase;
use ValueError;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\EnumValueMatchCondition
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\EnumValueMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\EnumValueMatchConditionBlueprint
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\IntType
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\StringType
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\QualifiedMatchConditionCompiler
 *
 * @internal
 */
final class EnumValueMatchConditionUnitTest extends TestCase
{
    use CatchException;

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

    public function testDoesNotCaptureIntEnumErrorWhenValueIsNotIntOrString(): void
    {
        $message = new SetDeliveryPriorityCommand(4.0);
        $originalException = $this->deliveryPriorityError(4);

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNull($matchedExceptionList);
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
