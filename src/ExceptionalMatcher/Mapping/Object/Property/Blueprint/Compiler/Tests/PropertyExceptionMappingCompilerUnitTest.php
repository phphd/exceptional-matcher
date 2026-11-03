<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
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

        $compilerId = ExceptionMappingCompiler::class.'<'.ReflectionProperty::class.','.PropertyExceptionMappingBlueprint::class.'>';

        $container->addCompilerPass(new PublicServiceCompilerPass($compilerId));

        $container->compile();

        /** @var ExceptionMappingCompiler<ReflectionProperty,PropertyExceptionMappingBlueprint> $compiler */
        $compiler = $container->get($compilerId);
        $this->compiler = $compiler;
    }

    public function testCatchAttributedPropertyIsIncludedRegardlessOfType(): void
    {
        $this->assertIncluded(new ReflectionProperty(ShippingAddress::class, 'postalCode'));
    }

    public function testFinalExceptionMappedClassTypedPropertyIsIncluded(): void
    {
        $this->assertIncluded(new ReflectionProperty(PlaceOrderCommand::class, 'shippingAddress'));
    }

    private function compile(ReflectionProperty $property): ?PropertyExceptionMappingBlueprint
    {
        return $this->compiler->compileBlueprint($property);
    }

    private function assertIncluded(ReflectionProperty $property): void
    {
        $blueprint = $this->compile($property);

        self::assertInstanceOf(
            PropertyExceptionMappingBlueprint::class,
            $blueprint,
            self::nameOf($property).' must be included in the blueprint.',
        );
        self::assertSame($property, $blueprint->getProperty());
    }

    private static function nameOf(ReflectionProperty $property): string
    {
        return $property->getDeclaringClass()->getName().'::$'.$property->getName();
    }
}
