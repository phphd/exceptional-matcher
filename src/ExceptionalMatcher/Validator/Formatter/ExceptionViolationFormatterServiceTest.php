<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator\Formatter;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Rule\Exception\Formatter\Delegating\DelegatingMatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Rule\Exception\Formatter\Delegating\Tests\Stub\CustomExceptionViolationFormatter;
use PhPhD\ExceptionalMatcher\Rule\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Validator\Formatter\Main\MainExceptionViolationFormatter;
use PhPhD\ExceptionalMatcher\Validator\Formatter\Validator\ValidationFailedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\ViolationListException;
use PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\ViolationListExceptionFormatter;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

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
        $violationFormatter = $this->get(MatchedExceptionFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>');
        self::assertInstanceOf(DelegatingMatchedExceptionFormatter::class, $violationFormatter);

        $defaultFormatter = $this->get(ExceptionViolationFormatter::class.'<Throwable>');
        self::assertInstanceOf(MainExceptionViolationFormatter::class, $defaultFormatter);

        $violationListExceptionFormatter = $this->get(ExceptionViolationFormatter::class.'<'.ViolationListException::class.'>');
        self::assertInstanceOf(ViolationListExceptionFormatter::class, $violationListExceptionFormatter);

        $validationFailedExceptionFormatter = $this->get(ExceptionViolationFormatter::class.'<'.ValidationFailedException::class.'>');
        self::assertInstanceOf(ValidationFailedExceptionFormatter::class, $validationFailedExceptionFormatter);

        $formatterRegistry = $this->getFormatterRegistry($violationFormatter);
        self::assertInstanceOf(ServiceLocator::class, $formatterRegistry);

        $providedServices = $formatterRegistry->getProvidedServices();
        krsort($providedServices);
        self::assertSame([
            ViolationListExceptionFormatter::class => ViolationListExceptionFormatter::class,
            ValidationFailedExceptionFormatter::class => ValidationFailedExceptionFormatter::class,
            MainExceptionViolationFormatter::class => MainExceptionViolationFormatter::class,
            CustomExceptionViolationFormatter::class => CustomExceptionViolationFormatter::class,
        ], $providedServices);

        self::assertSame($defaultFormatter, $formatterRegistry->get(MainExceptionViolationFormatter::class));
    }

    private function getFormatterRegistry(DelegatingMatchedExceptionFormatter $violationFormatter): ?ContainerInterface // @phpstan-ignore missingType.generics
    {
        /** @psalm-suppress InternalProperty, InaccessibleProperty, PossiblyNullFunctionCall, PossiblyNullReference */
        return (static fn (): ContainerInterface => $violationFormatter->formatterRegistry) // @phpstan-ignore-line
            ->bindTo(null, DelegatingMatchedExceptionFormatter::class)->__invoke()
        ;
    }

    private function get(string $id): mixed
    {
        return self::getContainer()->get($id);
    }
}
