<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\CompositeMatchConditionFactory;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Delegating\DelegatingMatchConditionFactory;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Uid\InvalidUidExceptionMatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Uid\InvalidUidExceptionMatchConditionFactory;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Validator\ValidationFailedExceptionMatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Validator\ValidationFailedExceptionMatchConditionFactory;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\ExceptionValueMatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\ExceptionValueMatchConditionFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Uid\Exception\InvalidArgumentException;
use Throwable;

use function class_exists;
use function krsort;

/**
 * @coversNothing
 *
 * @internal
 */
final class MatchConditionFactoryServiceTest extends BundleTestCase
{
    public function testConditionFactory(): void
    {
        $matchConditionFactory = self::getContainer()->get(MatchConditionFactory::class.'<'.Throwable::class.'>');
        self::assertInstanceOf(CompositeMatchConditionFactory::class, $matchConditionFactory);

        $conditionFactoryRegistry = $this->getConditionFactoryRegistry($matchConditionFactory);
        self::assertInstanceOf(ServiceLocator::class, $conditionFactoryRegistry);

        $providedServices = $conditionFactoryRegistry->getProvidedServices();
        krsort($providedServices);

        $expected = [
            ExceptionValueMatchCondition::class => ExceptionValueMatchConditionFactory::class,
            ValidationFailedExceptionMatchCondition::class => ValidationFailedExceptionMatchConditionFactory::class,
            InvalidUidExceptionMatchCondition::class => InvalidUidExceptionMatchConditionFactory::class,
        ];

        if (!class_exists(InvalidArgumentException::class)) {
            unset($expected[InvalidUidExceptionMatchCondition::class]);
        }

        self::assertSame($expected, $providedServices);
    }

    private function getConditionFactoryRegistry(CompositeMatchConditionFactory $matchConditionFactory): ?ContainerInterface // @phpstan-ignore missingType.generics
    {
        $factory = $this->getDelegatingMatchConditionFactory($matchConditionFactory);

        /** @psalm-suppress InternalProperty, InaccessibleProperty, PossiblyNullFunctionCall, PossiblyNullReference */
        return (static fn (): ContainerInterface => $factory->conditionFactoryRegistry) // @phpstan-ignore-line
            ->bindTo(null, DelegatingMatchConditionFactory::class)->__invoke()
        ;
    }

    private function getDelegatingMatchConditionFactory(CompositeMatchConditionFactory $matchConditionFactory): DelegatingMatchConditionFactory
    {
        /** @psalm-suppress InternalProperty, InaccessibleProperty, InvalidArrayAccess, PossiblyNullFunctionCall, PossiblyNullReference */
        return (static fn (): DelegatingMatchConditionFactory => $matchConditionFactory->factories[2]) // @phpstan-ignore-line
            ->bindTo(null, CompositeMatchConditionFactory::class)->__invoke()
        ;
    }
}
