<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\Tests;

use InvalidArgumentException;
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Exception\PropertyExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Exception\Tests\Trait\AssertPropertyCompilationFailedOn;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\PropertyExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\ExceptionValueMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\Tests\Stub\SuspendUserAccountCommand;
use PhPhD\ExceptionalMatcher\Tests\Probe\ProbeSet;
use PhPhD\ExceptionalMatcher\Tests\Trait\CatchException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\ExceptionValueMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\PropertyExceptionMappingCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Exception\PropertyExceptionMappingPrintCompilationFailedException
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingPrintCompilationFailedException::getRootException
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

    /** @dataProvider nonValueExceptionClassDataProvider */
    public function testThrowsWhenMatchConditionUsesNonValueExceptionClass(ReflectionProperty $property): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ExceptionValueMatchConditionCompiler::TYPE_GUARANTEE_MESSAGE);

        $compilationException = self::catchException(
            fn () => $this->compiler->compileBlueprint($property),
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
