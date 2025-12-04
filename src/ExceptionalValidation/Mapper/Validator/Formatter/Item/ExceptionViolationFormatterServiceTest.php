<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item;

use PhPhD\ExceptionalValidation\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Default\DefaultExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Delegating\DelegatingPropriatedExceptionFormatter;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Delegating\Tests\Stub\CustomExceptionFormatter;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Validator\ValidationFailedExceptionFormatter;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ViolationList\ViolationListException;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ViolationList\ViolationListExceptionFormatter;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Validator\Exception\ValidationFailedException;

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
        $violationFormatter = $this->get(PropriatedExceptionFormatter::class);
        self::assertInstanceOf(DelegatingPropriatedExceptionFormatter::class, $violationFormatter);

        $defaultFormatter = $this->get(PropriatedExceptionFormatter::class.'<Throwable>');
        self::assertInstanceOf(DefaultExceptionViolationFormatter::class, $defaultFormatter);

        $violationListExceptionFormatter = $this->get(PropriatedExceptionFormatter::class.'<'.ViolationListException::class.'>');
        self::assertInstanceOf(ViolationListExceptionFormatter::class, $violationListExceptionFormatter);

        $validationFailedExceptionFormatter = $this->get(PropriatedExceptionFormatter::class.'<'.ValidationFailedException::class.'>');
        self::assertInstanceOf(ValidationFailedExceptionFormatter::class, $validationFailedExceptionFormatter);

        $formatterRegistry = $this->getFormatterRegistry($violationFormatter);
        self::assertInstanceOf(ServiceLocator::class, $formatterRegistry);

        $providedServices = $formatterRegistry->getProvidedServices();
        krsort($providedServices);
        self::assertSame([
            ViolationListExceptionFormatter::class => ViolationListExceptionFormatter::class,
            ValidationFailedExceptionFormatter::class => ValidationFailedExceptionFormatter::class,
            CustomExceptionFormatter::class => CustomExceptionFormatter::class,
            DefaultExceptionViolationFormatter::class => DefaultExceptionViolationFormatter::class,
        ], $providedServices);

        self::assertSame($defaultFormatter, $formatterRegistry->get(DefaultExceptionViolationFormatter::class));
    }

    private function get(string $id): mixed
    {
        return self::getContainer()->get($id);
    }

    private function getFormatterRegistry(DelegatingPropriatedExceptionFormatter $violationFormatter): ?ContainerInterface // @phpstan-ignore missingType.generics
    {
        /** @psalm-suppress InternalProperty, InaccessibleProperty, PossiblyNullFunctionCall, PossiblyNullReference */
        return (static fn (): ContainerInterface => $violationFormatter->formatterRegistry) // @phpstan-ignore-line
            ->bindTo(null, DelegatingPropriatedExceptionFormatter::class)->__invoke()
        ;
    }
}
