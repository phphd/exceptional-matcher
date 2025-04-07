<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition;

use PhPhD\ExceptionalValidation\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite\CaptureMatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionValueMatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionValueMatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchConditionFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

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
        $matchConditionFactory = self::getContainer()->get('phd_exceptional_validation.match_condition_factory');
        self::assertInstanceOf(CaptureMatchConditionFactory::class, $matchConditionFactory);

        $conditionFactoryRegistry = $this->getConditionFactoryRegistry($matchConditionFactory);
        self::assertInstanceOf(ServiceLocator::class, $conditionFactoryRegistry);

        $providedServices = $conditionFactoryRegistry->getProvidedServices();
        krsort($providedServices);

        self::assertSame([
            ExceptionValueMatchCondition::class => ExceptionValueMatchConditionFactory::class,
            ValidationFailedExceptionValueMatchCondition::class => ValidationFailedExceptionValueMatchConditionFactory::class,
        ], $providedServices);
    }

    private function getConditionFactoryRegistry(CaptureMatchConditionFactory $matchConditionFactory): ?ContainerInterface
    {
        /**
         * @psalm-suppress InternalProperty
         * @psalm-suppress InaccessibleProperty
         */
        return (static fn (): ContainerInterface => $matchConditionFactory->conditionFactoryRegistry) // @phpstan-ignore-line
            ->bindTo(null, CaptureMatchConditionFactory::class)?->__invoke()
        ;
    }
}
