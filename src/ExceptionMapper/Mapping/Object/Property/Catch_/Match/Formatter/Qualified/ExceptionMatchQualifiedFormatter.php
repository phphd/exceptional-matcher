<?php

/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified;

use LogicException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\ExceptionMatch;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\ExceptionMatchFormatter;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * @internal
 *
 * @template TException of Throwable
 *
 * @template TResult of mixed
 *
 * @implements ExceptionMatchFormatter<TException,TResult>
 */
final class ExceptionMatchQualifiedFormatter implements ExceptionMatchFormatter
{
    /**
     * @api
     *
     * @template TFormatter of ExceptionMatchFormatter<TException,TResult>
     *
     * @param ContainerInterface<class-string<TFormatter>,TFormatter> $formatterRegistry
     * @param ?class-string<TFormatter> $defaultFormatter
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

        return $this->getFormatter($formatterId)->format($exceptionMatch); // @phpstan-ignore argument.templateType (that's a fair catch)
    }

    /**
     * @template TFormatter of ExceptionMatchFormatter<TException,TResult>
     *
     * @param class-string<TFormatter> $formatterId
     *
     * @return TFormatter
     *
     * @internal
     */
    public function getFormatter(string $formatterId): ExceptionMatchFormatter
    {
        if (!$this->formatterRegistry->has($formatterId)) {
            throw new UnregisteredExceptionMatchFormatterException($formatterId);
        }

        /** @psalm-suppress ArgumentTypeCoercion - Psalm degrades class-string<TFormatter> to class-string */
        return $this->formatterRegistry->get($formatterId);
    }
}
