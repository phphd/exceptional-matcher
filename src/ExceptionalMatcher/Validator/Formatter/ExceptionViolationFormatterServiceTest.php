<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator\Formatter;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Exception\Formatter\Delegating\DelegatingMatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Exception\Formatter\Delegating\Tests\Stub\CustomExceptionViolationFormatter;
use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Validator\Formatter\Embedded\ViolationsEmbeddedException;
use PhPhD\ExceptionalMatcher\Validator\Formatter\Embedded\ViolationsEmbeddedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Validator\Formatter\Main\MainExceptionViolationFormatter;
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

        $violationListExceptionFormatter = $this->get(ExceptionViolationFormatter::class.'<'.ViolationsEmbeddedException::class.'>');
        self::assertInstanceOf(ViolationsEmbeddedExceptionFormatter::class, $violationListExceptionFormatter);

        $validationFailedExceptionFormatter = $this->get(ExceptionViolationFormatter::class.'<'.ValidationFailedException::class.'>');
        self::assertInstanceOf(ViolationsEmbeddedExceptionFormatter::class, $validationFailedExceptionFormatter);

        self::assertSame($violationListExceptionFormatter, $validationFailedExceptionFormatter);

        $formatterRegistry = $this->getFormatterRegistry($violationFormatter);
        self::assertInstanceOf(ServiceLocator::class, $formatterRegistry);

        $providedServices = $formatterRegistry->getProvidedServices();
        krsort($providedServices);
        self::assertSame([
            MainExceptionViolationFormatter::class => MainExceptionViolationFormatter::class,
            ViolationsEmbeddedExceptionFormatter::class => ViolationsEmbeddedExceptionFormatter::class,
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
