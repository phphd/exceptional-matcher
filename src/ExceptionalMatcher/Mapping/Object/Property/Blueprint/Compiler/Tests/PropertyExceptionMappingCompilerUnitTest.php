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
use ReflectionClass;
use ReflectionProperty;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\PropertyExceptionMappingCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Type\PossiblyExceptionMappedType
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\PropertyTypeAnalyser
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\AnyType
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\MixedType
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\MixedObjectType
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\MixedIterableType
 *
 * @internal
 */
final class PropertyExceptionMappingCompilerUnitTest extends TestCase
{
    private const PROBED_CLASSES = [
        PlaceOrderCommand::class,
        ShippingAddress::class,
        Subcategory::class,
    ];

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

    /** @dataProvider catchAttributedPropertyDataProvider */
    public function testCatchAttributedPropertyIsIncludedRegardlessOfType(ReflectionProperty $property): void
    {
        $this->assertIsIncluded($property);
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function catchAttributedPropertyDataProvider(): iterable
    {
        return self::probesOf(InclusionRule::CatchAttributedPropertyIsIncluded);
    }

    /** @dataProvider looselyTypedPropertyDataProvider */
    public function testLooselyTypedPropertyIsIncluded(ReflectionProperty $property): void
    {
        $this->assertIsIncluded($property);
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function looselyTypedPropertyDataProvider(): iterable
    {
        return self::probesOf(InclusionRule::LooselyTypedPropertyIsIncluded);
    }

    /** @dataProvider iterableTypedPropertyDataProvider */
    public function testIterableTypedPropertyIsIncluded(ReflectionProperty $property): void
    {
        $this->assertIsIncluded($property);
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function iterableTypedPropertyDataProvider(): iterable
    {
        return self::probesOf(InclusionRule::IterableTypedPropertyIsIncluded);
    }

    /** @dataProvider interfaceTypedPropertyDataProvider */
    public function testInterfaceTypedPropertyIsIncluded(ReflectionProperty $property): void
    {
        $this->assertIsIncluded($property);
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function interfaceTypedPropertyDataProvider(): iterable
    {
        return self::probesOf(InclusionRule::InterfaceTypedPropertyIsIncluded);
    }

    /** @dataProvider nonFinalClassTypedPropertyDataProvider */
    public function testNonFinalClassTypedPropertyIsIncluded(ReflectionProperty $property): void
    {
        $this->assertIsIncluded($property);
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function nonFinalClassTypedPropertyDataProvider(): iterable
    {
        return self::probesOf(InclusionRule::NonFinalClassTypedPropertyIsIncluded);
    }

    /** @dataProvider finalExceptionMappedClassTypedPropertyDataProvider */
    public function testFinalExceptionMappedClassTypedPropertyIsIncluded(ReflectionProperty $property): void
    {
        $this->assertIsIncluded($property);
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function finalExceptionMappedClassTypedPropertyDataProvider(): iterable
    {
        return self::probesOf(InclusionRule::FinalExceptionMappedClassTypedPropertyIsIncluded);
    }

    /** @dataProvider finalNonExceptionMappedClassTypedPropertyDataProvider */
    public function testFinalNonExceptionMappedClassTypedPropertyIsSkipped(ReflectionProperty $property): void
    {
        self::assertNull(
            $this->compile($property),
            self::nameOf($property).' must be skipped from the blueprint.',
        );
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function finalNonExceptionMappedClassTypedPropertyDataProvider(): iterable
    {
        return self::probesOf(InclusionRule::FinalNonExceptionMappedClassTypedPropertyIsSkipped);
    }

    /** @dataProvider scalarTypedPropertyDataProvider */
    public function testScalarTypedPropertyIsSkipped(ReflectionProperty $property): void
    {
        self::assertNull(
            $this->compile($property),
            self::nameOf($property).' must be skipped from the blueprint.',
        );
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function scalarTypedPropertyDataProvider(): iterable
    {
        return self::probesOf(InclusionRule::ScalarTypedPropertyIsSkipped);
    }

    /** @dataProvider enumTypedPropertyDataProvider */
    public function testEnumTypedPropertyIsSkipped(ReflectionProperty $property): void
    {
        self::assertNull(
            $this->compile($property),
            self::nameOf($property).' must be skipped from the blueprint.',
        );
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function enumTypedPropertyDataProvider(): iterable
    {
        return self::probesOf(InclusionRule::EnumTypedPropertyIsSkipped);
    }

    /** @dataProvider builtInClassTypedPropertyDataProvider */
    public function testBuiltInClassTypedPropertyIsSkipped(ReflectionProperty $property): void
    {
        self::assertNull(
            $this->compile($property),
            self::nameOf($property).' must be skipped from the blueprint.',
        );
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function builtInClassTypedPropertyDataProvider(): iterable
    {
        return self::probesOf(InclusionRule::BuiltInClassTypedPropertyIsSkipped);
    }

    /** @dataProvider relativeClassTypedPropertyDataProvider */
    public function testRelativeClassTypedPropertyIsIncluded(ReflectionProperty $property): void
    {
        $this->assertIsIncluded($property);
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function relativeClassTypedPropertyDataProvider(): iterable
    {
        return self::probesOf(InclusionRule::RelativeClassTypedPropertyIsIncluded);
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

    /** @return iterable<string,array{ReflectionProperty}> */
    private static function probesOf(InclusionRule $rule): iterable
    {
        foreach (self::PROBED_CLASSES as $className) {
            foreach ((new ReflectionClass($className))->getProperties() as $property) {
                $probe = self::probeOf($property);

                if (null === $probe || $probe->rule !== $rule) {
                    continue;
                }

                yield $probe->label ?? self::nameOf($property) => [$property];
            }
        }
    }

    private static function probeOf(ReflectionProperty $property): ?ProbeOf
    {
        foreach ($property->getAttributes(ProbeOf::class) as $attribute) {
            return $attribute->newInstance();
        }

        return null;
    }

    private static function nameOf(ReflectionProperty $property): string
    {
        return $property->getDeclaringClass()->getName().'::$'.$property->getName();
    }
}
