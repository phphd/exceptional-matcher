<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\CompositeMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Delegating\DelegatingMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\EnumValueMatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\EnumValueMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Integration\Uid\InvalidUidExceptionMatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Integration\Uid\InvalidUidExceptionMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Integration\Validator\ValidationFailedExceptionMatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Integration\Validator\ValidationFailedExceptionMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\ExceptionValueMatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\ExceptionValueMatchConditionCompiler;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;
use Throwable;

use function class_exists;
use function krsort;
use function property_exists;

/**
 * @coversNothing
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
        $factory = $this->getDelegatingMatchConditionCompiler($compiler);

        /** @psalm-suppress InternalProperty, InaccessibleProperty, PossiblyNullFunctionCall, PossiblyNullReference */
        return (static fn (): ContainerInterface => $factory->matchConditionCompilerRegistry) // @phpstan-ignore-line
            ->bindTo(null, DelegatingMatchConditionCompiler::class)->__invoke()
        ;
    }

    private function getDelegatingMatchConditionCompiler(CompositeMatchConditionCompiler $compiler): DelegatingMatchConditionCompiler
    {
        /** @psalm-suppress InternalProperty, InaccessibleProperty, InvalidArrayAccess, PossiblyNullFunctionCall, PossiblyNullReference */
        return (static fn (): DelegatingMatchConditionCompiler => $compiler->compilers[2]) // @phpstan-ignore-line
            ->bindTo(null, CompositeMatchConditionCompiler::class)->__invoke()
        ;
    }
}
