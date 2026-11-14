<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Symfony\Validator;

use PhPhD\ExceptionMapper\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\ExceptionMatchFormatter;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\ExceptionMatchQualifiedFormatter;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Symfony\Validator\Embedded\ViolationsEmbeddedException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Symfony\Validator\Embedded\ViolationsEmbeddedExceptionFormatter;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Symfony\Validator\Violation\ExceptionMatchToViolationFormatter;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Tests\Stub\Cart\OutOfStockExceptionMatchFormatter;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Tests\Stub\Cart\OutOfStockExceptionMatchFormatterCompilerPass;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Tests\Stub\Message\ExceptionMessageFormatter;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Tests\Stub\Message\ExceptionMessageFormatterCompilerPass;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

use function krsort;

/**
 * @covers \PhPhD\ExceptionMapper\Bundle\PhdExceptionMapperBundle
 * @covers \PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension
 *
 * @internal
 */
final class ExceptionMatchToViolationQualifiedFormatterServiceTest extends BundleTestCase
{
    /** @return array<int,CompilerPassInterface> */
    protected static function compilerPasses(): array
    {
        return [
            ExceptionMessageFormatterCompilerPass::PRIORITY + 1 => new ExceptionMessageFormatterCompilerPass(),
            OutOfStockExceptionMatchFormatterCompilerPass::PRIORITY => new OutOfStockExceptionMatchFormatterCompilerPass(),
        ];
    }

    public function testViolationFormatter(): void
    {
        $exceptionMatchToViolationQualifiedFormatter = $this->get(ExceptionMatchFormatter::class.'<?,'.ConstraintViolationInterface::class.'>');
        self::assertInstanceOf(ExceptionMatchQualifiedFormatter::class, $exceptionMatchToViolationQualifiedFormatter);

        $defaultFormatter = $this->get(ExceptionMatchFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>');
        self::assertInstanceOf(ExceptionMatchToViolationFormatter::class, $defaultFormatter);

        $violationsEmbeddedExceptionFormatter = $this->get(ExceptionMatchFormatter::class.'<'.ViolationsEmbeddedException::class.','.ConstraintViolationInterface::class.'>');
        self::assertInstanceOf(ViolationsEmbeddedExceptionFormatter::class, $violationsEmbeddedExceptionFormatter);

        $validationFailedExceptionFormatter = $this->get(ExceptionMatchFormatter::class.'<'.ValidationFailedException::class.','.ConstraintViolationInterface::class.'>');
        self::assertInstanceOf(ViolationsEmbeddedExceptionFormatter::class, $validationFailedExceptionFormatter);

        self::assertSame($violationsEmbeddedExceptionFormatter, $validationFailedExceptionFormatter);

        $formatterRegistry = $this->getFormatterRegistry($exceptionMatchToViolationQualifiedFormatter);
        self::assertInstanceOf(ServiceLocator::class, $formatterRegistry);

        $providedServices = $formatterRegistry->getProvidedServices();
        krsort($providedServices);
        self::assertNotContains(
            ExceptionMessageFormatter::class,
            $providedServices, // excluded from the registry because of different TResult
        );
        self::assertSame([
            OutOfStockExceptionMatchFormatter::class => OutOfStockExceptionMatchFormatter::class,
            ExceptionMatchToViolationFormatter::class => ExceptionMatchToViolationFormatter::class,
            ViolationsEmbeddedExceptionFormatter::class => ViolationsEmbeddedExceptionFormatter::class,
        ], $providedServices);

        self::assertSame($defaultFormatter, $formatterRegistry->get(ExceptionMatchToViolationFormatter::class));
    }

    /** @param ExceptionMatchQualifiedFormatter<Throwable,mixed> $exceptionMatchFormatter */
    private function getFormatterRegistry(ExceptionMatchQualifiedFormatter $exceptionMatchFormatter): ?ContainerInterface // @phpstan-ignore missingType.generics
    {
        /** @psalm-suppress InternalProperty, InaccessibleProperty, PossiblyNullFunctionCall, PossiblyNullReference */
        return (static fn(): ContainerInterface => $exceptionMatchFormatter->formatterRegistry) // @phpstan-ignore-line
            ->bindTo(newThis: null, newScope: ExceptionMatchQualifiedFormatter::class)->__invoke()
        ;
    }

    private function get(string $id): mixed
    {
        return self::getContainer()->get($id);
    }
}
