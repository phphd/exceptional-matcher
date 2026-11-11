<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified;

use LogicException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\ExceptionMatch;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\ExceptionMatchFormatter;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMatchFormatter<Throwable,mixed>
 */
final class QualifiedExceptionMatchFormatter implements ExceptionMatchFormatter
{
    /**
     * @api
     *
     * @template T of ExceptionMatchFormatter
     *
     * @phpstan-param ContainerInterface<class-string<T>,T> $formatterRegistry
     * @phpstan-param ?class-string<ExceptionMatchFormatter<Throwable,mixed>> $defaultFormatter
     *
     * @psalm-param ContainerInterface<class-string<ExceptionMatchFormatter>,ExceptionMatchFormatter> $formatterRegistry
     * @psalm-param ?class-string<ExceptionMatchFormatter> $defaultFormatter
     */
    public function __construct(
        private readonly ContainerInterface $formatterRegistry,
        private readonly ?string $defaultFormatter = null,
    ) {
    }

    public function format(ExceptionMatch $exceptionMatch): array
    {
        $catch = $exceptionMatch->getNode();

        $formatterId = $catch->getFormatterId() ?? $this->defaultFormatter;

        if (null === $formatterId) {
            throw new LogicException('No ExceptionMatchFormatter provided and no default one configured.');
        }

        return $this->getFormatter($formatterId)->format($exceptionMatch);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     *
     * @return ExceptionMatchFormatter<Throwable,mixed>
     *
     * @internal
     *
     * @phpstan-param class-string<ExceptionMatchFormatter<Throwable,mixed>> $formatterId
     *
     * @psalm-param class-string<ExceptionMatchFormatter> $formatterId
     */
    public function getFormatter(string $formatterId): ExceptionMatchFormatter
    {
        if (!$this->formatterRegistry->has($formatterId)) {
            throw new UnregisteredExceptionFormatterException($formatterId);
        }

        /** @psalm-var ExceptionMatchFormatter<Throwable,mixed> */
        return $this->formatterRegistry->get($formatterId);
    }
}
