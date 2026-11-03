<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Category\Subcategory;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\PlaceOrderCommand;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Order\Shipping\ShippingAddress;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\PropertyExceptionMappingBlueprint;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\PropertyExceptionMappingCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Type\PossiblyExceptionMappedType
 *
 * @internal
 */
final class PropertyExceptionMappingCompilerUnitTest extends TestCase
{
    /** @var ExceptionMappingCompiler<ReflectionProperty,PropertyExceptionMappingBlueprint> */
    private ExceptionMappingCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->addCompilerPass(new PublicServiceCompilerPass(ExceptionMappingCompiler::class.'<'.ReflectionProperty::class.','.PropertyExceptionMappingBlueprint::class.'>'));

        $container->compile();

        /** @var ExceptionMappingCompiler<ReflectionProperty,PropertyExceptionMappingBlueprint> $compiler */
        $compiler = $container->get(ExceptionMappingCompiler::class.'<'.ReflectionProperty::class.','.PropertyExceptionMappingBlueprint::class.'>');
        $this->compiler = $compiler;
    }

    public function testCatchAttributedPropertyIsIncludedRegardlessOfType(): void
    {
        $this->assertIsIncluded(new ReflectionProperty(ShippingAddress::class, 'postalCode'));
    }

    /**
     * @param class-string $className
     *
     * @dataProvider looselyTypedPropertyDataProvider
     */
    public function testLooselyTypedPropertyIsIncluded(string $className, string $propertyName): void
    {
        $this->assertIsIncluded(new ReflectionProperty($className, $propertyName));
    }

    /** @return iterable<array{class-string,string}> */
    public static function looselyTypedPropertyDataProvider(): iterable
    {
        yield [PlaceOrderCommand::class, 'customerNote'];

        yield [PlaceOrderCommand::class, 'metadata'];

        yield [PlaceOrderCommand::class, 'gatewayPayload'];
    }

    /**
     * @param class-string $className
     *
     * @dataProvider iterableTypedPropertyDataProvider
     */
    public function testIterableTypedPropertyIsIncluded(string $className, string $propertyName): void
    {
        $this->assertIsIncluded(new ReflectionProperty($className, $propertyName));
    }

    /** @return iterable<array{class-string,string}> */
    public static function iterableTypedPropertyDataProvider(): iterable
    {
        yield [PlaceOrderCommand::class, 'couponCodes'];

        yield [PlaceOrderCommand::class, 'giftCardCodes'];

        yield [PlaceOrderCommand::class, 'loyaltyRewards'];

        yield [PlaceOrderCommand::class, 'lines'];
    }

    public function testInterfaceTypedPropertyIsIncluded(): void
    {
        $this->assertIsIncluded(new ReflectionProperty(PlaceOrderCommand::class, 'paymentMethod'));
    }

    /**
     * @param class-string $className
     *
     * @dataProvider nonFinalClassTypedPropertyDataProvider
     */
    public function testNonFinalClassTypedPropertyIsIncluded(string $className, string $propertyName): void
    {
        $this->assertIsIncluded(new ReflectionProperty($className, $propertyName));
    }

    /** @return iterable<string,array{class-string,string}> */
    public static function nonFinalClassTypedPropertyDataProvider(): iterable
    {
        yield 'abstract class' => [PlaceOrderCommand::class, 'discount'];

        yield 'concrete class' => [PlaceOrderCommand::class, 'customer'];
    }

    public function testFinalExceptionMappedClassTypedPropertyIsIncluded(): void
    {
        $this->assertIsIncluded(new ReflectionProperty(PlaceOrderCommand::class, 'shippingAddress'));
    }

    public function testFinalNonExceptionMappedClassTypedPropertyIsSkipped(): void
    {
        $courierTip = new ReflectionProperty(PlaceOrderCommand::class, 'courierTip');

        self::assertNull(
            $this->compile($courierTip),
            self::nameOf($courierTip).' must be skipped from the blueprint.',
        );
    }

    public function testScalarTypedPropertyIsSkipped(): void
    {
        $loyaltyPointsToSpend = new ReflectionProperty(PlaceOrderCommand::class, 'loyaltyPointsToSpend');

        self::assertNull(
            $this->compile($loyaltyPointsToSpend),
            self::nameOf($loyaltyPointsToSpend).' must be skipped from the blueprint.',
        );
    }

    public function testEnumTypedPropertyIsSkipped(): void
    {
        $shippingMethod = new ReflectionProperty(PlaceOrderCommand::class, 'shippingMethod');

        self::assertNull(
            $this->compile($shippingMethod),
            self::nameOf($shippingMethod).' must be skipped from the blueprint.',
        );
    }

    /**
     * @param class-string $className
     *
     * @dataProvider builtInClassTypedPropertyDataProvider
     */
    public function testBuiltInClassTypedPropertyIsSkipped(string $className, string $propertyName): void
    {
        $property = new ReflectionProperty($className, $propertyName);

        self::assertNull(
            $this->compile($property),
            self::nameOf($property).' must be skipped from the blueprint.',
        );
    }

    /** @return iterable<string,array{class-string,string}> */
    public static function builtInClassTypedPropertyDataProvider(): iterable
    {
        yield 'class' => [PlaceOrderCommand::class, 'requestedDeliveryDate'];

        yield 'interface' => [PlaceOrderCommand::class, 'recipientPhone'];
    }

    /**
     * @param class-string $className
     *
     * @dataProvider relativeClassTypedPropertyDataProvider
     */
    public function testRelativeClassTypedPropertyIsIncluded(string $className, string $propertyName): void
    {
        $this->assertIsIncluded(new ReflectionProperty($className, $propertyName));
    }

    /** @return iterable<array{class-string,string}> */
    public static function relativeClassTypedPropertyDataProvider(): iterable
    {
        yield [Subcategory::class, 'parentSubcategory'];

        yield [Subcategory::class, 'rootCategory'];
    }

    private function compile(ReflectionProperty $property): ?PropertyExceptionMappingBlueprint
    {
        return $this->compiler->compileBlueprint($property);
    }

    private function assertIsIncluded(ReflectionProperty $property): void
    {
        $blueprint = $this->compile($property);

        self::assertInstanceOf(
            PropertyExceptionMappingBlueprint::class,
            $blueprint,
            self::nameOf($property).' must be included in the blueprint.',
        );
        self::assertSame(
            $property,
            $blueprint->getProperty(),
            self::nameOf($property).' blueprint must reference the very probed property.',
        );
    }

    private static function nameOf(ReflectionProperty $property): string
    {
        return $property->getDeclaringClass()->getName().'::$'.$property->getName();
    }
}
