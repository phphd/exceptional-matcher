<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests;

use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\ExceptionMapper;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\ChooseDeliveryMethodCommand;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\Method\DeliveryMethod;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\Priority\DeliveryPriority;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\Tests\Stub\SetDeliveryPriorityCommand;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\ExceptionMatchList;
use PhPhD\ExceptionMapper\Tests\Trait\CatchException;
use PHPUnit\Framework\TestCase;
use ValueError;

/**
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\EnumValueMatchCondition
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\EnumValueMatchConditionCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\EnumValueMatchConditionBlueprint
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Type\Specification\IntType
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Type\Specification\StringType
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\QualifiedMatchConditionCompiler
 *
 * @internal
 */
final class EnumValueMatchConditionUnitTest extends TestCase
{
    use CatchException;

    /** @var ExceptionMapper<ExceptionMatchList> */
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionMapperExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMapper<ExceptionMatchList> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<'.ExceptionMatchList::class.'>');
        $this->mapper = $mapper;
    }

    public function testDoesNotCaptureStringEnumErrorWhenRuleValueIsNull(): void
    {
        $message = new ChooseDeliveryMethodCommand(deliveryMethod: null);
        $originalException = $this->deliveryMethodError('lockr');

        $exceptionMatches = $this->mapper->map($originalException, $message);

        self::assertNull($exceptionMatches);
    }

    public function testDoesNotCaptureStringEnumErrorWhenValueDiffers(): void
    {
        $message = new ChooseDeliveryMethodCommand('courier');
        $originalException = $this->deliveryMethodError('courrier');

        $exceptionMatches = $this->mapper->map($originalException, $message);

        self::assertNull($exceptionMatches);
    }

    public function testCapturesStringEnumErrorWhenValueIsSame(): void
    {
        $message = new ChooseDeliveryMethodCommand('lockr');
        $originalException = $this->deliveryMethodError('lockr');

        $exceptionMatches = $this->mapper->map($originalException, $message);

        self::assertNotNull($exceptionMatches);
    }

    public function testDoesNotCaptureIntEnumErrorWhenRuleValueIsNull(): void
    {
        $message = new SetDeliveryPriorityCommand(priority: null);
        $originalException = $this->deliveryPriorityError(4);

        $exceptionMatches = $this->mapper->map($originalException, $message);

        self::assertNull($exceptionMatches);
    }

    public function testDoesNotCaptureIntEnumErrorWhenValueDiffers(): void
    {
        $message = new SetDeliveryPriorityCommand(3);
        $originalException = $this->deliveryPriorityError(4);

        $exceptionMatches = $this->mapper->map($originalException, $message);

        self::assertNull($exceptionMatches);
    }

    public function testCapturesIntEnumErrorWhenValueIsSame(): void
    {
        $message = new SetDeliveryPriorityCommand(4);
        $originalException = $this->deliveryPriorityError(4);

        $exceptionMatches = $this->mapper->map($originalException, $message);

        self::assertNotNull($exceptionMatches);
    }

    public function testDoesNotCaptureIntEnumErrorWhenValueIsNotIntOrString(): void
    {
        $message = new SetDeliveryPriorityCommand(4.0);
        $originalException = $this->deliveryPriorityError(4);

        $exceptionMatches = $this->mapper->map($originalException, $message);

        self::assertNull($exceptionMatches);
    }

    private function deliveryMethodError(string $value): ValueError
    {
        return self::catchException(static fn() => DeliveryMethod::from($value), ValueError::class);
    }

    private function deliveryPriorityError(int $value): ValueError
    {
        return self::catchException(static fn() => DeliveryPriority::from($value), ValueError::class);
    }
}
