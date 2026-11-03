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
use PhPhD\ExceptionalMatcher\Tests\Probe\ProbeSet;
use PHPUnit\Framework\TestCase;
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
        return self::probes()->of(PropertyInclusionRule::CatchAttributedPropertyIsIncluded);
    }

    /**
     * @dataProvider looselyTypedPropertyDataProvider
     * @dataProvider iterableTypedPropertyDataProvider
     * @dataProvider interfaceTypedPropertyDataProvider
     * @dataProvider nonFinalClassTypedPropertyDataProvider
     * @dataProvider finalExceptionMappedClassTypedPropertyDataProvider
     * @dataProvider relativeClassTypedPropertyDataProvider
     */
    public function testPossiblyExceptionMappedPropertyIsIncluded(ReflectionProperty $property): void
    {
        $this->assertIsIncluded($property);
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function looselyTypedPropertyDataProvider(): iterable
    {
        return self::probes()->of(PropertyInclusionRule::LooselyTypedPropertyIsIncluded);
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function iterableTypedPropertyDataProvider(): iterable
    {
        return self::probes()->of(PropertyInclusionRule::IterableTypedPropertyIsIncluded);
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function interfaceTypedPropertyDataProvider(): iterable
    {
        return self::probes()->of(PropertyInclusionRule::InterfaceTypedPropertyIsIncluded);
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function nonFinalClassTypedPropertyDataProvider(): iterable
    {
        return self::probes()->of(PropertyInclusionRule::NonFinalClassTypedPropertyIsIncluded);
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function finalExceptionMappedClassTypedPropertyDataProvider(): iterable
    {
        return self::probes()->of(PropertyInclusionRule::FinalExceptionMappedClassTypedPropertyIsIncluded);
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function relativeClassTypedPropertyDataProvider(): iterable
    {
        return self::probes()->of(PropertyInclusionRule::RelativeClassTypedPropertyIsIncluded);
    }

    /**
     * @dataProvider finalNonExceptionMappedClassTypedPropertyDataProvider
     * @dataProvider scalarTypedPropertyDataProvider
     * @dataProvider enumTypedPropertyDataProvider
     * @dataProvider builtInClassTypedPropertyDataProvider
     */
    public function testNeverExceptionMappedPropertyIsSkipped(ReflectionProperty $property): void
    {
        self::assertNull(
            $this->compile($property),
            ProbeSet::referenceOf($property).' must be skipped from the blueprint.',
        );
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function finalNonExceptionMappedClassTypedPropertyDataProvider(): iterable
    {
        return self::probes()->of(PropertyInclusionRule::FinalNonExceptionMappedClassTypedPropertyIsSkipped);
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function scalarTypedPropertyDataProvider(): iterable
    {
        return self::probes()->of(PropertyInclusionRule::ScalarTypedPropertyIsSkipped);
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function enumTypedPropertyDataProvider(): iterable
    {
        return self::probes()->of(PropertyInclusionRule::EnumTypedPropertyIsSkipped);
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function builtInClassTypedPropertyDataProvider(): iterable
    {
        return self::probes()->of(PropertyInclusionRule::BuiltInClassTypedPropertyIsSkipped);
    }

    private static function probes(): ProbeSet
    {
        return new ProbeSet(self::PROBED_CLASSES);
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
            ProbeSet::referenceOf($property).' must be included in the blueprint.',
        );
        self::assertSame(
            $property,
            $blueprint->getProperty(),
            ProbeSet::referenceOf($property).' blueprint must reference the very probed property.',
        );
    }
}
