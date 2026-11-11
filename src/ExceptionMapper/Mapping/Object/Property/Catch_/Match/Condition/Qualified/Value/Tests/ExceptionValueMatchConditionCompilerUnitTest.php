<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value\Tests;

use InvalidArgumentException;
use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionMapper\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionMapper\Mapping\Blueprint\ExceptionMappingBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Exception\PropertyExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Exception\Tests\Trait\AssertPropertyCompilationFailedOn;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\PropertyExceptionMappingBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value\ExceptionValueMatchConditionCompiler;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value\Tests\Stub\SuspendUserAccountCommand;
use PhPhD\ExceptionMapper\Tests\Probe\ProbeSet;
use PhPhD\ExceptionMapper\Tests\Trait\CatchException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value\ExceptionValueMatchConditionCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\PropertyExceptionMappingCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Exception\PropertyExceptionMappingPrintCompilationFailedException
 * @covers \PhPhD\ExceptionMapper\Mapping\Blueprint\Compiler\ExceptionMappingPrintCompilationFailedException::getRootException
 *
 * @internal
 */
final class ExceptionValueMatchConditionCompilerUnitTest extends TestCase
{
    use CatchException;
    use AssertPropertyCompilationFailedOn;

    private const PROBED_CLASSES = [
        SuspendUserAccountCommand::class,
    ];

    /** @var ExceptionMappingCompiler<ReflectionProperty,PropertyExceptionMappingBlueprint> */
    private ExceptionMappingCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionMapperExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->addCompilerPass(new PublicServiceCompilerPass(ExceptionMappingCompiler::class.'<'.ReflectionProperty::class.','.PropertyExceptionMappingBlueprint::class.'>'));

        $container->compile();

        /** @var ExceptionMappingCompiler<ReflectionProperty,PropertyExceptionMappingBlueprint> $compiler */
        $compiler = $container->get(ExceptionMappingCompiler::class.'<'.ReflectionProperty::class.','.PropertyExceptionMappingBlueprint::class.'>');
        $this->compiler = $compiler;
    }

    /** @dataProvider nonValueExceptionClassDataProvider */
    public function testThrowsWhenMatchConditionUsesNonValueExceptionClass(ReflectionProperty $property): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ExceptionValueMatchConditionCompiler::EXCEPTION_TYPE_GUARANTEE_MESSAGE);

        $compilationException = self::catchException(
            fn(): ?ExceptionMappingBlueprint => $this->compiler->compile($property),
            PropertyExceptionMappingPrintCompilationFailedException::class,
        );

        self::assertPropertyCompilationFailedOn($property, $compilationException);

        throw $compilationException->getRootException();
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public static function nonValueExceptionClassDataProvider(): iterable
    {
        return self::probes()->of(ExceptionValueConditionRule::NonValueExceptionClassThrows);
    }

    private static function probes(): ProbeSet
    {
        return new ProbeSet(self::PROBED_CLASSES);
    }
}
