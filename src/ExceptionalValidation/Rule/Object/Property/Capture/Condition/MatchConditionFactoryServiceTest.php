<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition;

use PhPhD\ExceptionalValidation\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite\CompositeMatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Delegating\DelegatingMatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionMatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionMatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchConditionFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Throwable;

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

        self::assertSame([
            ExceptionValueMatchCondition::class => ExceptionValueMatchConditionFactory::class,
            ValidationFailedExceptionMatchCondition::class => ValidationFailedExceptionMatchConditionFactory::class,
        ], $providedServices);
    }

    private function getConditionFactoryRegistry(CompositeMatchConditionFactory $captureMatchConditionFactory): ?ContainerInterface // @phpstan-ignore missingType.generics
    {
        $factory = $this->getDelegatingMatchConditionFactory($captureMatchConditionFactory);

        /** @psalm-suppress InternalProperty, InaccessibleProperty */
        return (static fn (): ContainerInterface => $factory->conditionFactoryRegistry) // @phpstan-ignore-line
            ->bindTo(null, DelegatingMatchConditionFactory::class)?->__invoke()
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
