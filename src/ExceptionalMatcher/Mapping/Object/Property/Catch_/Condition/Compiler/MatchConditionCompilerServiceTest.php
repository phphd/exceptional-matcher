<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\EnumValueMatchCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum\EnumValueMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Uid\InvalidUidExceptionMatchCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Uid\InvalidUidExceptionMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Validator\ValidationFailedExceptionMatchCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Validator\ValidationFailedExceptionMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\QualifiedMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\ExceptionValueMatchCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\ExceptionValueMatchConditionCompiler;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;
use Throwable;

use function class_exists;
use function krsort;
use function property_exists;

/**
 * @covers \PhPhD\ExceptionalMatcher\Bundle\PhdExceptionalMatcherBundle
 * @covers \PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension
 *
 * @internal
 */
final class MatchConditionCompilerServiceTest extends BundleTestCase
{
    public function testConditionCompilers(): void
    {
        $matchConditionCompiler = self::getContainer()->get(MatchConditionCompiler::class.'<'.Throwable::class.'>');
        self::assertInstanceOf(CompositeMatchConditionCompiler::class, $matchConditionCompiler);

        $conditionCompilerRegistry = $this->getConditionCompilerRegistry($matchConditionCompiler);
        self::assertInstanceOf(ServiceLocator::class, $conditionCompilerRegistry);

        $providedServices = $conditionCompilerRegistry->getProvidedServices();
        krsort($providedServices);

        $expected = [
            ExceptionValueMatchCondition::class => ExceptionValueMatchConditionCompiler::class,
            ValidationFailedExceptionMatchCondition::class => ValidationFailedExceptionMatchConditionCompiler::class,
            InvalidUidExceptionMatchCondition::class => InvalidUidExceptionMatchConditionCompiler::class,
            EnumValueMatchCondition::class => EnumValueMatchConditionCompiler::class,
        ];

        if (!class_exists(InvalidUidException::class) || !property_exists(InvalidUidException::class, 'invalidValue')) {
            unset($expected[InvalidUidExceptionMatchCondition::class]);
        }

        self::assertSame($expected, $providedServices);
    }

    private function getConditionCompilerRegistry(CompositeMatchConditionCompiler $compiler): ?ContainerInterface // @phpstan-ignore missingType.generics
    {
        $factory = $this->getQualifiedMatchConditionCompiler($compiler);

        /** @psalm-suppress InternalProperty, InaccessibleProperty, PossiblyNullFunctionCall, PossiblyNullReference */
        return (static fn (): ContainerInterface => $factory->matchConditionCompilerRegistry) // @phpstan-ignore-line
            ->bindTo(newThis: null, newScope: QualifiedMatchConditionCompiler::class)->__invoke()
        ;
    }

    private function getQualifiedMatchConditionCompiler(CompositeMatchConditionCompiler $compiler): QualifiedMatchConditionCompiler
    {
        /** @psalm-suppress InternalProperty, InaccessibleProperty, InvalidArrayAccess, PossiblyNullFunctionCall, PossiblyNullReference */
        return (static fn (): QualifiedMatchConditionCompiler => $compiler->compilers[2]) // @phpstan-ignore-line
            ->bindTo(newThis: null, newScope: CompositeMatchConditionCompiler::class)->__invoke()
        ;
    }
}
