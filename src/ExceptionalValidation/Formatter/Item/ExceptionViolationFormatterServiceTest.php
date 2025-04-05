<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter\Item;

use PhPhD\ExceptionalValidation\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalValidation\Formatter\Item\Delegating\DelegatingExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Formatter\Item\Validator\ViolationListExceptionFormatter;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\CustomExceptionViolationFormatter;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

use function krsort;

/**
 * @coversNothing
 *
 * @internal
 */
final class ExceptionViolationFormatterServiceTest extends BundleTestCase
{
    public function testViolationFormatter(): void
    {
        $violationFormatter = self::getContainer()->get('phd_exceptional_validation.violation_formatter');
        self::assertInstanceOf(DelegatingExceptionViolationFormatter::class, $violationFormatter);

        $defaultFormatter = self::getContainer()->get('phd_exceptional_validation.violation_formatter.default');
        self::assertInstanceOf(DefaultExceptionViolationFormatter::class, $defaultFormatter);

        $violationListExceptionFormatter = self::getContainer()->get('phd_exceptional_validation.violation_formatter.violation_list_exception');
        self::assertInstanceOf(ViolationListExceptionFormatter::class, $violationListExceptionFormatter);

        $formatterRegistry = $this->getFormatterRegistry($violationFormatter);
        self::assertInstanceOf(ServiceLocator::class, $formatterRegistry);

        $providedServices = $formatterRegistry->getProvidedServices();
        krsort($providedServices);
        self::assertSame([
            'default' => DefaultExceptionViolationFormatter::class,
            CustomExceptionViolationFormatter::class => CustomExceptionViolationFormatter::class,
            ViolationListExceptionFormatter::class => ViolationListExceptionFormatter::class,
        ], $providedServices);

        self::assertSame($defaultFormatter, $formatterRegistry->get('default'));
    }

    private function getFormatterRegistry(DelegatingExceptionViolationFormatter $violationFormatter): ?ContainerInterface
    {
        /**
         * @psalm-suppress InternalProperty
         * @psalm-suppress InaccessibleProperty
         */
        return (static fn (): ContainerInterface => $violationFormatter->formatterRegistry) // @phpstan-ignore-line
            ->bindTo(null, DelegatingExceptionViolationFormatter::class)?->__invoke()
        ;
    }
}
