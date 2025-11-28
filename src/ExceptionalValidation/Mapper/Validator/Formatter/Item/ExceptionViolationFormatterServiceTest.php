<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item;

use PhPhD\ExceptionalValidation\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Default\DefaultExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Delegating\DelegatingExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Delegating\Tests\Stub\CustomExceptionViolationFormatter;
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
        $violationFormatter = $this->get(ExceptionViolationFormatter::class);
        self::assertInstanceOf(DelegatingExceptionViolationFormatter::class, $violationFormatter);

        $defaultFormatter = $this->get(ExceptionViolationFormatter::class.'<Throwable>');
        self::assertInstanceOf(DefaultExceptionViolationFormatter::class, $defaultFormatter);

        $violationListExceptionFormatter = $this->get(ExceptionViolationFormatter::class.'<'.ViolationListException::class.'>');
        self::assertInstanceOf(ViolationListExceptionFormatter::class, $violationListExceptionFormatter);

        $validationFailedExceptionFormatter = $this->get(ExceptionViolationFormatter::class.'<'.ValidationFailedException::class.'>');
        self::assertInstanceOf(ValidationFailedExceptionFormatter::class, $validationFailedExceptionFormatter);

        $formatterRegistry = $this->getFormatterRegistry($violationFormatter);
        self::assertInstanceOf(ServiceLocator::class, $formatterRegistry);

        $providedServices = $formatterRegistry->getProvidedServices();
        krsort($providedServices);
        self::assertSame([
            'default' => DefaultExceptionViolationFormatter::class,
            ViolationListExceptionFormatter::class => ViolationListExceptionFormatter::class,
            ValidationFailedExceptionFormatter::class => ValidationFailedExceptionFormatter::class,
            CustomExceptionViolationFormatter::class => CustomExceptionViolationFormatter::class,
        ], $providedServices);

        self::assertSame($defaultFormatter, $formatterRegistry->get('default'));
    }

    private function get(string $id): mixed
    {
        return self::getContainer()->get($id);
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
